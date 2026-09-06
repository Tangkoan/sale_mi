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
use App\Jobs\PrintKitchenJob;
use App\Jobs\PrintInvoiceJob;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

// Library សម្រាប់ Print
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Illuminate\Support\Facades\File;

// Library សម្រាប់ថតរូបវិក្កយបត្រ
use Spatie\Browsershot\Browsershot; 

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

                // ✅ បញ្ជាឲ្យ Job ធ្វើការ Print ទៅចុងភៅនៅ Background ជំនួសការ Print ផ្ទាល់
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

    private function printOrderToKitchen($orderId)
    {
        $itemsToPrint = OrderItem::with([
                'product.category.kitchenDestination', 
                'addons.addon.kitchenDestination', 
                'order.table' 
            ])
            ->where('order_id', $orderId)
            ->where('is_printed', false)
            ->get();

        if ($itemsToPrint->isEmpty()) { return; }

        $kitchenBatches = [];
        foreach ($itemsToPrint as $item) {
            // ឆែកមើលតើវាជាមុខម្ហូប 'Extra' (Standalone Addon) ដែរឬទេ?
            $isWrapperProduct = stripos($item->product?->name, 'extra') !== false;

            if ($isWrapperProduct && $item->addons->isNotEmpty()) {
                // ប្រសិនបើជា Extra, យើងត្រូវទាញ Addon ធ្វើជា Main Product វិញ
                foreach ($item->addons as $addon) {
                    $destination = $addon->addon?->kitchenDestination;
                    if (!$destination) {
                        $destination = $item->product?->category?->kitchenDestination;
                    }

                    if (!$destination || !$destination->is_active) { continue; }
                    
                    $batchKey = $destination->id;
                    if (!isset($kitchenBatches[$batchKey])) {
                        $kitchenBatches[$batchKey] = ['info' => $destination, 'items' => []];
                    }

                    // បង្កើត Item ក្លែងក្លាយ ដើម្បីឲ្យចង្ក្រានចេញឈ្មោះ Addon ជាម្ហូបគោល
                    $fakeItem = clone $item;
                    $fakeItem->quantity = floatval($item->quantity) * floatval($addon->quantity ?? 1);
                    
                    $fakeProduct = clone $item->product;
                    $fakeProduct->name = $addon->addon->name ?? $addon->name ?? 'Addon'; // ប្តូរឈ្មោះ Extra ទៅជាឈ្មោះ Addon
                    
                    $fakeItem->setRelation('product', $fakeProduct);
                    $fakeItem->setRelation('addons', collect()); // លុប Addon List ចោល ព្រោះវាជា Main Product ហើយ
                    
                    $kitchenBatches[$batchKey]['items'][] = $fakeItem;
                }
            } else {
                // ដំណើរការម្ហូបធម្មតា
                $destination = $item->product?->category?->kitchenDestination;
                if (!$destination || !$destination->is_active) { continue; }
                
                $batchKey = $destination->id;
                if (!isset($kitchenBatches[$batchKey])) {
                    $kitchenBatches[$batchKey] = ['info' => $destination, 'items' => []];
                }
                $kitchenBatches[$batchKey]['items'][] = $item;
            }
        }

        foreach ($kitchenBatches as $batchKey => $batch) {
            $printerInfo = $batch['info'];
            $items       = $batch['items'];
            $ipAddress   = $printerInfo->printnode_id; 

            try {
                $firstItem = $items[0];
                $tableName = $firstItem->order->table->name ?? ('Table: ' . $firstItem->order->table_id);

                $html = \Illuminate\Support\Facades\View::make('pos.kitchen_receipt', compact('printerInfo', 'items', 'tableName'))->render();
                $imagePath = storage_path('app/kitchen_receipt_' . uniqid() . '.png');
                $chromePath = env('CHROME_PATH', 'C:\Program Files\Google\Chrome\Application\chrome.exe');

                \Spatie\Browsershot\Browsershot::html($html)
                    ->setChromePath($chromePath)
                    ->windowSize(576, 100)
                    ->fullPage()           
                    ->save($imagePath);

                $connector = new NetworkPrintConnector($ipAddress, 9100, 3);
                $printer = new Printer($connector);

                $image = EscposImage::load($imagePath, false);
                $printer->bitImageColumnFormat($image);
                
                $printer->feed(1);
                $printer->cut();
                $printer->close();

                if (file_exists($imagePath)) { unlink($imagePath); }

                foreach ($items as $item) {
                    $item->update(['is_printed' => true]);
                }
            } catch (\Exception $e) {
                Log::error("❌ Print Error: " . $e->getMessage());
            }
        }
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
                // 🔥 ចំណុចសំខាន់៖ បើព្រីនរួច បង្កើត Row ថ្មី!
                if ($item->is_printed) {
                    $newItem = $item->replicate();
                    $newItem->quantity = 1;
                    $newItem->is_printed = false;
                    $newItem->save();

                    foreach ($item->addons as $addon) {
                        $newAddon = $addon->replicate();
                        $newAddon->order_item_id = $newItem->id;
                        $newAddon->save();
                    }
                } else {
                    $item->increment('quantity');
                }
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

            // បញ្ជាព្រីនសម្រាប់មុខម្ហូបដែលបន្ថែមថ្មី (is_printed = false)
            if ($request->action === 'increase') {
                PrintKitchenJob::dispatch($item->order_id);
            }

            return response()->json(['status' => 'success', 'total' => $newTotal]);
        });
    }

    // 🔥 FUNCTION ថ្មីសម្រាប់ប្ដូរម្ហូប
    public function exchangeItem(Request $request)
    {
        $request->validate([
            'old_item_id'    => 'required|exists:order_items,id',
            'exchange_qty'   => 'required|integer|min:1',
            'new_product_id' => 'required|exists:products,id',
            'new_qty'        => 'required|integer|min:1', // 🔥 ទទួលយកចំនួនម្ហូបថ្មី
            'new_addons'     => 'nullable|array'
        ]);

        return DB::transaction(function () use ($request) {
            try {
                $oldItem = OrderItem::with('product', 'addons')->findOrFail($request->old_item_id);
                $order = Order::findOrFail($oldItem->order_id);

                if ($request->exchange_qty > $oldItem->quantity) {
                    throw new \Exception("ចំនួនដែលចង់ប្ដូរ ធំជាងចំនួនដែលមានស្រាប់!");
                }

                $oldProductName = $oldItem->product ? $oldItem->product->name : 'ម្ហូបចាស់';

                if ($request->exchange_qty == $oldItem->quantity) {
                    OrderItemAddon::where('order_item_id', $oldItem->id)->delete();
                    $oldItem->delete();
                } else {
                    $oldItem->decrement('quantity', $request->exchange_qty);
                }

                $newProduct = Product::find($request->new_product_id);
                $newItem = OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $newProduct->id,
                    'quantity'   => $request->new_qty, // 🔥 ប្រើប្រាស់ចំនួនថ្មី
                    'price'      => $newProduct->price,
                    'note'       => '🔄 ប្ដូរចេញពី: ' . $oldProductName,
                    'is_printed' => false,
                    'status'     => 'pending',
                    'created_by' => Auth::id(),
                ]);

                if (!empty($request->new_addons)) {
                    foreach ($request->new_addons as $addon) {
                        OrderItemAddon::create([
                            'order_item_id' => $newItem->id,
                            'addon_id'      => $addon['id'],
                            'price'         => $addon['price'],
                            'quantity'      => $addon['qty'] ?? 1
                        ]);
                    }
                }

                $newTotal = $this->recalculateOrderTotal($order->id);
                PrintKitchenJob::dispatch($order->id);

                return response()->json([
                    'status'    => 'success',
                    'message'   => 'បានប្ដូរមុខម្ហូបជោគជ័យ!',
                    'new_total' => $newTotal
                ]);

            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
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

                // ១. ទាញយកឈ្មោះតុទាំងពីរ ដើម្បីយកមកតភ្ជាប់គ្នា
                $mainTable = Table::find($request->main_table_id);
                $targetTable = Table::find($request->target_table_id);

                foreach ($targetOrder->items as $item) {
                    $item->update(['order_id' => $mainOrder->id]);
                }

                $targetOrder->delete();
                Table::where('id', $request->target_table_id)->update(['status' => 'available']);
                $newTotal = $this->recalculateOrderTotal($mainOrder->id);

                // ២. 🔥 កូដបន្ថែមថ្មី៖ តភ្ជាប់ឈ្មោះតុ ហើយ Save ចូល Order
                // ឆែកមើលថាតើ Order នេះធ្លាប់ Merge ពីមុនមកឬអត់ (បើធ្លាប់ យកឈ្មោះចាស់មកបន្ត បើមិនធ្លាប់ យកឈ្មោះតុ Main)
                $currentMergedNames = $mainOrder->merged_table_names ?: $mainTable->name;
                $mainOrder->merged_table_names = $currentMergedNames . ' & ' . $targetTable->name;
                $mainOrder->save();

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
            $mainOrder = Order::with('table')->findOrFail($request->order_id);

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

            // =========================================================
            // 🔥 កូដថ្មីបន្ថែមនៅទីនេះ សម្រាប់ចាប់យកឈ្មោះតុដែលបាន Merge
            // =========================================================
            $otherOrderIds = array_unique(array_diff($affectedOrderIds, [$mainOrder->id]));
            $mergedTablesList = []; 

            foreach ($otherOrderIds as $oldOrderId) {
                $oldOrder = Order::with('table')->find($oldOrderId);
                if ($oldOrder) {
                    // ទាញយកឈ្មោះតុចាស់ដែលត្រូវប៉ះពាល់យកមកកត់ត្រា
                    if ($oldOrder->table) {
                        $mergedTablesList[] = $oldOrder->table->name;
                    }

                    // លុប Order ចាស់ចោលប្រសិនបើអស់ម្ហូប
                    if ($oldOrder->items()->count() == 0) {
                        if ($oldOrder->table_id) {
                            Table::where('id', $oldOrder->table_id)->update(['status' => 'available']);
                        }
                        $oldOrder->delete();
                    }
                }
            }

            // តភ្ជាប់ឈ្មោះតុទាំងអស់ចូលគ្នា (ឧទាហរណ៍៖ A-3 & T-01)
            $mergedNames = null;
            if (count($mergedTablesList) > 0) {
                $mainTableName = $mainOrder->table ? $mainOrder->table->name : 'ទូទៅ';
                $allTables = array_merge([$mainTableName], $mergedTablesList);
                $mergedNames = implode(' & ', array_unique($allTables)); // លុបឈ្មោះស្ទួនចេញ រួចភ្ជាប់គ្នា
            }
            // =========================================================

            $totalAmount = $this->recalculateOrderTotal($mainOrder->id);
            $change = $request->received_amount - $totalAmount;

            if ($request->payment_method == 'cash' && $change < 0) {
                return response()->json(['status' => 'error', 'message' => 'ទឹកប្រាក់ដែលទទួលបានមិនគ្រប់គ្រាន់ទេ!'], 422);
            }

            $mainOrder->update([
                'note' => $request->delivery_platform ? 'Delivery: ' . $request->delivery_platform : null,
                'status'             => 'completed',
                'total_amount'       => $totalAmount,
                'payment_method'     => $request->payment_method,
                'received_amount'    => $request->received_amount,
                'change_amount'      => $change,
                'paid_at'            => now(),
                'check_out_time'     => now(), 
                'merged_table_names' => $mergedNames // 🔥 Save ឈ្មោះតុដែលជាប់គ្នាចូល Database
            ]);

            if ($mainOrder->table_id) {
                Table::where('id', $mainOrder->table_id)->update(['status' => 'available']);
            }

            $paymentDetails = [
                'received_amount' => $request->received_amount,
                'payment_method'  => $request->payment_method,
                'change_amount'   => $change,
            ];

            // បញ្ជាឲ្យ Job ធ្វើការ Print វិក្កយបត្រ (Invoice) នៅ Background
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

            $paymentDetails = [
                'received_amount' => $request->received_amount,
                'payment_method'  => $request->payment_method,
                'change_amount'   => $change,
            ];

            // ✅ បញ្ជាឲ្យ Job ធ្វើការ Print វិក្កយបត្របំបែក (Split Invoice) នៅ Background
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

    /**
     * 🔥 FUNCTION: បោះវិក្កយបត្រទៅ Network Printer 
     */
    private function printInvoiceToNetwork($orderId, $paymentDetails)
    {
        $printer = null;
        try {
            $order = Order::with(['items.product', 'items.addons.addon', 'table', 'user'])->find($orderId);
            if (!$order) return;

            $groupedItems = [];

            foreach ($order->items as $item) {
                $noteKey = $item->note ? strtolower(trim($item->note)) : '';
                
                // ឆែកមើលតើវាជាមុខម្ហូប 'Extra' (Standalone Addon) ដែរឬទេ?
                $isExtra = stripos($item->product?->name, 'extra') !== false;

                if ($isExtra && $item->addons && $item->addons->isNotEmpty()) {
                    // បើជា Extra (Standalone Addon) យើងទាញ Addon មកធ្វើជាម្ហូបគោល (Main Product) តែម្តង
                    foreach ($item->addons as $addon) {
                        $addonName = $addon->addon->name ?? $addon->name ?? 'Addon';
                        $addonPrice = floatval($addon->price);
                        $addonQty = floatval($item->quantity) * floatval($addon->quantity ?? 1);

                        // បង្កើត Row Key ថ្មីសម្រាប់ Addon ដើម្បីអាចបូកបញ្ចូលគ្នាបាន
                        $rowKey = 'addon_standalone_' . ($addon->addon_id ?? $addonName) . '_' . $addonPrice . '_' . $noteKey;

                        if (array_key_exists($rowKey, $groupedItems)) {
                            $groupedItems[$rowKey]->quantity += $addonQty;
                        } else {
                            $fakeItem = clone $item;
                            $fakeItem->quantity = $addonQty;
                            $fakeItem->price = $addonPrice;
                            
                            // បង្កើត Product ក្លែងក្លាយដើម្បីឲ្យ Invoice លោតឈ្មោះ Addon ជំនួស Extra
                            $fakeProduct = clone $item->product;
                            $fakeProduct->name = $addonName;
                            $fakeItem->setRelation('product', $fakeProduct);
                            
                            // លុប Addon ចេញពី List ព្រោះយើងបានប្រែក្លាយវាទៅជា Main Product ហើយ
                            $fakeItem->setRelation('addons', collect());

                            $groupedItems[$rowKey] = $fakeItem;
                        }
                    }
                } else {
                    // ដំណើរការធម្មតាសម្រាប់ម្ហូបទូទៅ
                    $rowKey = $item->product_id . '_' . floatval($item->price) . '_' . $noteKey;

                    if (array_key_exists($rowKey, $groupedItems)) {
                        $groupedItems[$rowKey]->quantity += $item->quantity;

                        if ($item->addons) {
                            $existingAddons = $groupedItems[$rowKey]->addons;
                            
                            foreach ($item->addons as $incomingAddon) {
                                $incomingId = $incomingAddon->addon_id ?? $incomingAddon->name ?? 'unknown';
                                $found = false;

                                foreach ($existingAddons as $exAddon) {
                                    $exId = $exAddon->addon_id ?? $exAddon->name ?? 'unknown';
                                    if ($exId == $incomingId) {
                                        $currentQty = floatval($exAddon->quantity ?? 1);
                                        $addQty = floatval($incomingAddon->quantity ?? 1);
                                        $exAddon->quantity = $currentQty + $addQty;
                                        $found = true;
                                        break;
                                    }
                                }

                                if (!$found) {
                                    $existingAddons->push(clone $incomingAddon);
                                }
                            }
                        }
                    } else {
                        $clonedItem = clone $item;
                        $clonedAddons = collect();
                        
                        if ($item->addons) {
                            foreach ($item->addons as $addon) {
                                $addonId = $addon->addon_id ?? $addon->name ?? 'unknown';
                                
                                $existing = $clonedAddons->first(function($a) use ($addonId) {
                                    $id = $a->addon_id ?? $a->name ?? 'unknown';
                                    return $id == $addonId;
                                });

                                if ($existing) {
                                    $existing->quantity = floatval($existing->quantity ?? 1) + floatval($addon->quantity ?? 1);
                                } else {
                                    $clonedAddons->push(clone $addon);
                                }
                            }
                        }
                        $clonedItem->setRelation('addons', $clonedAddons);
                        $groupedItems[$rowKey] = $clonedItem;
                    }
                }
            }

            // ជំនួស Items ចាស់ដោយ Items ដែលបានរៀបចំស្អាតរួច
            $order->setRelation('items', collect(array_values($groupedItems)));

            $cashierDestination = KitchenDestination::where('name', 'like', '%អ្នកគិតលុយ%')->first();
            if (!$cashierDestination || empty($cashierDestination->printnode_id)) {
                throw new \Exception("រកមិនឃើញ IP សម្រាប់ម៉ាស៊ីនអ្នកគិតលុយទេ!");
            }
            $ipAddress = $cashierDestination->printnode_id;
            $shop = ShopInfo::first();

            $html = \Illuminate\Support\Facades\View::make('pos.invoice_receipt', compact('order', 'paymentDetails', 'shop'))->render();
            $imagePath = storage_path('app/invoice_' . uniqid() . '.png');
            $chromePath = env('CHROME_PATH', 'C:\Program Files\Google\Chrome\Application\chrome.exe');

            \Spatie\Browsershot\Browsershot::html($html)
                ->setChromePath($chromePath)
                ->windowSize(576, 800)
                ->fullPage()           
                ->save($imagePath);

            $connector = new NetworkPrintConnector($ipAddress, 9100, 3);
            $printer = new Printer($connector);

            $image = EscposImage::load($imagePath, false);
            $printer->bitImageColumnFormat($image);
            
            $printer->feed(2);
            $printer->cut();

            if (file_exists($imagePath)) { unlink($imagePath); }

        } catch (\Throwable $e) {
            Log::error("❌ Invoice Print Error: " . $e->getMessage());
        } finally {
            if ($printer !== null) {
                try { $printer->close(); } catch (\Throwable $t) {}
            }
        }
    }
}