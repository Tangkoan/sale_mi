<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddon;
use App\Models\Table;
use App\Models\Product;
use App\Models\ShopInfo;
use App\Models\KitchenDestination;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

// Import ឯកសារ Job ដែលយើងទើបតែបង្កើត
use App\Jobs\PrintKitchenJob;
use App\Jobs\PrintInvoiceJob;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'table_id' => 'required',
            'items'    => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.addons'     => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation Error',
                'errors'  => $validator->errors()
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            try {
                $order = Order::firstOrCreate(
                    ['table_id' => $request->table_id, 'status' => 'pending'],
                    [
                        'invoice_number' => 'INV-' . time() . '-' . $request->table_id,
                        'user_id'        => Auth::id(),
                        'total_amount'   => 0,
                        'check_in_time'  => now(),
                    ]
                );

                $table = Table::find($request->table_id);
                if ($table) {
                    $table->update(['status' => 'busy']);
                }

                foreach ($request->items as $itemData) {
                    $product = Product::find($itemData['product_id']);
                    
                    if (!$product) {
                        throw new \Exception("Product ID {$itemData['product_id']} not found.");
                    }

                    $orderItem = OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $itemData['product_id'],
                        'quantity'   => $itemData['qty'],
                        'price'      => $itemData['price'],
                        'note'       => $itemData['note'] ?? null,
                        'is_printed' => false, 
                        'status'     => 'pending',
                        'created_by' => Auth::id(),
                    ]);

                    if (!empty($itemData['addons'])) {
                        foreach ($itemData['addons'] as $addon) {
                            OrderItemAddon::create([
                                'order_item_id' => $orderItem->id,
                                'addon_id'      => $addon['id'],
                                'price'         => $addon['price'],
                                'quantity'      => $addon['qty'] ?? 1
                            ]);
                        }
                    }
                }
                
                $this->recalculateOrderTotal($order->id);

                // ============================================================
                // 🔥 ហៅ Job សម្រាប់ព្រីនទៅផ្ទះបាយ (Kitchen) ជំនួសការហៅផ្ទាល់
                // ============================================================
                PrintKitchenJob::dispatch($order->id);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Order placed successfully!',
                    'order_id' => $order->id
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        });
    }

    public function updateItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'action'  => 'required|in:increase,decrease,remove',
        ]);

        return DB::transaction(function () use ($request) {
            $item = OrderItem::with('addons')->findOrFail($request->item_id);
            
            if ($request->action === 'remove') {
                OrderItemAddon::where('order_item_id', $item->id)->delete();
                $item->delete();
            } 
            elseif ($request->action === 'increase') {
                $item->increment('quantity');
            } 
            elseif ($request->action === 'decrease') {
                if ($item->quantity > 1) {
                    $item->decrement('quantity');
                } else {
                    OrderItemAddon::where('order_item_id', $item->id)->delete();
                    $item->delete();
                }
            }
            $newTotal = $this->recalculateOrderTotal($item->order_id);
            return response()->json(['status' => 'success', 'total' => $newTotal]);
        });
    }

    public function getItemsForMerge($tableId)
    {
        $order = Order::with(['items.product', 'items.addons.addon'])
                      ->where('table_id', $tableId)
                      ->where('status', 'pending')
                      ->first();

        if (!$order) {
            return response()->json(['items' => []]);
        }

        return response()->json(['items' => $order->items, 'source_order_id' => $order->id ]);
    }

    public function getOrderDetails($tableId)
    {
        try {
            $order = Order::with(['items.product', 'items.addons.addon', 'table']) 
                ->where('table_id', $tableId)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if (!$order) {
                return response()->json(['status' => 'error', 'message' => 'No active order found'], 404);
            }

            $order->items->transform(function($item) {
                $addonTotal = 0;
                if ($item->addons) {
                    foreach ($item->addons as $addon) {
                        $qty = intval($addon->quantity ?? 1); 
                        $price = floatval($addon->price ?? 0);
                        $addonTotal += ($price * $qty);
                    }
                }
                $item->unit_price_calculated = $item->price + $addonTotal;
                $item->total_line_price_calculated = $item->unit_price_calculated * $item->quantity;
                return $item;
            });

            $shop = ShopInfo::first();
            $timezone = 'Asia/Phnom_Penh';
            $dateFormatted = $order->created_at->setTimezone($timezone)->format('d/m/Y h:i A');

            return response()->json([
                'status' => 'success',
                'order'  => $order,
                'items'  => $order->items, 
                'formatted_date' => $dateFormatted,
                'shop' => $shop
            ]);

        } catch (\Exception $e) {
            Log::error("Order Details Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server Error'], 500);
        }
    }

    private function recalculateOrderTotal($orderId)
    {
        $order = Order::with(['items.addons'])->find($orderId); 
        $totalAmount = 0;

        if ($order && $order->items) {
            foreach ($order->items as $item) {
                $itemTotal = $item->price * $item->quantity;
                $addonTotal = 0;
                foreach ($item->addons ?? [] as $addon) { 
                    $addonTotal += ($addon->price * ($addon->quantity ?? 1));
                }
                $totalAmount += ($itemTotal + $addonTotal);
            }
            $order->update(['total_amount' => $totalAmount]);
        }
        return $totalAmount;
    }

    public function getBusyTablesForMerge(Request $request)
    {
        $currentTableId = $request->query('current');
        if (!$currentTableId) return response()->json([]);

        $tables = Table::where('status', 'busy')
                    ->where('id', '!=', $currentTableId)
                    ->select('id', 'name')
                    ->orderBy('name', 'asc')
                    ->get();
        return response()->json($tables);
    }

    public function mergeTables(Request $request)
    {
        $request->validate([
            'target_table_id' => 'required',
            'main_table_id'   => 'required',
        ]);

        return DB::transaction(function () use ($request) {
            try {
                $mainOrder = Order::where('table_id', $request->main_table_id)->where('status', 'pending')->first();
                $targetOrder = Order::where('table_id', $request->target_table_id)->where('status', 'pending')->first();

                if (!$mainOrder) throw new \Exception("តុបច្ចុប្បន្នគ្មាន Order ដើម្បីបញ្ចូលទេ");
                if (!$targetOrder) throw new \Exception("តុដែលត្រូវបញ្ចូល (Target) គ្មាន Order ទេ");

                foreach ($targetOrder->items as $item) {
                    $item->update(['order_id' => $mainOrder->id]);
                }

                $targetOrder->delete();
                Table::where('id', $request->target_table_id)->update(['status' => 'available']);
                $newTotal = $this->recalculateOrderTotal($mainOrder->id);

                return response()->json([
                    'status' => 'success', 
                    'message' => 'បញ្ចូលតុជោគជ័យ!',
                    'new_total' => $newTotal
                ]);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
            }
        });
    }

    public function moveTable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_table_id' => 'required',
            'target_table_id'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $targetTable = Table::find($request->target_table_id);
                if (!$targetTable) throw new \Exception("រកមិនឃើញតុគោលដៅ (ID: {$request->target_table_id})");
                if ($targetTable->status !== 'available') throw new \Exception("តុ {$targetTable->name} មិនទំនេរទេ (Status: {$targetTable->status})");

                $order = Order::where('table_id', $request->current_table_id)->where('status', 'pending')->first();
                if (!$order) throw new \Exception("តុបច្ចុប្បន្នគ្មានការកម្មង់ទេ (ឬត្រូវបានគិតលុយរួចរាល់)");

                $order->update(['table_id' => $request->target_table_id]);
                Table::where('id', $request->current_table_id)->update(['status' => 'available']);
                $targetTable->update(['status' => 'busy']);

                return response()->json([
                    'status'  => 'success',
                    'message' => "បានប្ដូរទៅតុ {$targetTable->name} ជោគជ័យ!"
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage()], 500);
        }
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'order_id'       => 'required|exists:orders,id',
            'received_amount'=> 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qr,card',
            'items'          => 'required|array', 
        ]);

        return DB::transaction(function () use ($request) {
            $mainOrder = Order::findOrFail($request->order_id);

            if ($mainOrder->status == 'completed') {
                return response()->json(['status' => 'error', 'message' => 'Order is already paid!'], 400);
            }

            $submittedItemIds = collect($request->items)->pluck('id')->filter()->toArray();

            OrderItem::where('order_id', $mainOrder->id)
                     ->whereNotIn('id', $submittedItemIds)
                     ->delete(); 

            $affectedOrderIds = [$mainOrder->id];

            foreach ($request->items as $itemData) {
                $item = OrderItem::find($itemData['id']);
                if ($item) {
                    if ($item->order_id != $mainOrder->id) {
                        $affectedOrderIds[] = $item->order_id;
                    }
                    $item->update([
                        'quantity' => $itemData['quantity'],
                        'order_id' => $mainOrder->id 
                    ]);

                    if (!empty($itemData['addons'])) {
                        $submittedAddonIds = collect($itemData['addons'])->pluck('id')->toArray();
                        OrderItemAddon::where('order_item_id', $item->id)->whereNotIn('id', $submittedAddonIds)->delete();
                        foreach ($itemData['addons'] as $addonData) {
                            OrderItemAddon::where('id', $addonData['id'])->update(['quantity' => $addonData['quantity']]);
                        }
                    } else {
                        $item->addons()->delete();
                    }
                }
            }

            $otherOrderIds = array_unique(array_diff($affectedOrderIds, [$mainOrder->id]));
            foreach ($otherOrderIds as $oldOrderId) {
                $oldOrder = Order::find($oldOrderId);
                if ($oldOrder && $oldOrder->items()->count() == 0) {
                    if ($oldOrder->table_id) {
                        Table::where('id', $oldOrder->table_id)->update(['status' => 'available']);
                    }
                    $oldOrder->delete();
                }
            }

            $totalAmount = $this->recalculateOrderTotal($mainOrder->id);
            $change = $request->received_amount - $totalAmount;

            if ($request->payment_method == 'cash' && $change < 0) {
                return response()->json(['status' => 'error', 'message' => 'ទឹកប្រាក់ដែលទទួលបានមិនគ្រប់គ្រាន់ទេ!'], 422);
            }

            $mainOrder->update([
                'status'          => 'completed',
                'total_amount'    => $totalAmount,
                'payment_method'  => $request->payment_method,
                'received_amount' => $request->received_amount,
                'change_amount'   => $change,
                'paid_at'         => now(),
                'check_out_time'  => now(), 
            ]);

            if ($mainOrder->table_id) {
                Table::where('id', $mainOrder->table_id)->update(['status' => 'available']);
            }

            // ============================================================
            // 🔥 ហៅ Job សម្រាប់ព្រីនវិក្កយបត្រ (Invoice)
            // ============================================================
            $paymentDetails = [
                'received_amount' => $request->received_amount,
                'payment_method'  => $request->payment_method,
                'change_amount'   => $change,
            ];

            PrintInvoiceJob::dispatch($mainOrder->id, $paymentDetails);

            return response()->json([
                'status'   => 'success',
                'message'  => 'ការទូទាត់ប្រាក់ជោគជ័យ!',
                'change'   => $change,
            ]);
        });
    }

    public function splitPayment(Request $request)
    {
        $request->validate([
            'original_order_id' => 'required|exists:orders,id',
            'split_items'       => 'required|array|min:1', 
            'payment_method'    => 'required',
            'received_amount'   => 'required|numeric|min:0'
        ]);

        return DB::transaction(function () use ($request) {
            $originalOrder = Order::findOrFail($request->original_order_id);

            $splitOrder = Order::create([
                'invoice_number'  => 'SPL-' . time(),
                'user_id'         => Auth::id(),
                'table_id'        => $originalOrder->table_id, 
                'status'          => 'completed', 
                'payment_method'  => $request->payment_method,
                'received_amount' => $request->received_amount,
                'total_amount'    => 0, 
                'paid_at'         => now()
            ]);

            foreach ($request->split_items as $splitItem) {
                $originalItem = OrderItem::with('addons')->find($splitItem['id']);
                if (!$originalItem) continue;

                $qtyToSplit = intval($splitItem['qty']);

                if ($qtyToSplit >= $originalItem->quantity) {
                    $originalItem->update(['order_id' => $splitOrder->id]);
                } else {
                    $originalItem->decrement('quantity', $qtyToSplit);
                    $newItem = $originalItem->replicate();
                    $newItem->order_id = $splitOrder->id;
                    $newItem->quantity = $qtyToSplit;
                    $newItem->save();

                    foreach ($originalItem->addons as $addon) {
                        $newAddon = $addon->replicate();
                        $newAddon->order_item_id = $newItem->id;
                        $newAddon->save();
                    }
                }
            }

            $splitTotal = $this->recalculateOrderTotal($splitOrder->id);
            $this->recalculateOrderTotal($originalOrder->id);
            $change = $request->received_amount - $splitTotal;

            if ($request->payment_method == 'cash' && $change < 0) {
                throw new \Exception('ទឹកប្រាក់ដែលទទួលបានមិនគ្រប់គ្រាន់ទេ!');
            }

            $splitOrder->update(['total_amount' => $splitTotal, 'change_amount' => $change]);

            if ($originalOrder->items()->count() == 0) {
                $originalOrder->update(['status' => 'completed']);
                Table::where('id', $originalOrder->table_id)->update(['status' => 'available']);
            }

            // ============================================================
            // 🔥 ហៅ Job សម្រាប់ព្រីនវិក្កយបត្របំបែក (Split Invoice)
            // ============================================================
            $paymentDetails = [
                'received_amount' => $request->received_amount,
                'payment_method'  => $request->payment_method,
                'change_amount'   => $change,
            ];

            PrintInvoiceJob::dispatch($splitOrder->id, $paymentDetails);

            return response()->json([
                'status' => 'success',
                'message' => 'បំបែកការគិតលុយជោគជ័យ!',
                'split_order_id' => $splitOrder->id,
                'remaining_items_count' => $originalOrder->items()->count(),
                'change' => $change
            ]);
        });
    }
}