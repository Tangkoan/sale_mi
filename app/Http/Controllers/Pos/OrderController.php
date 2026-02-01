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

class OrderController extends Controller
{
    // ... (store function នៅដដែល)

    public function store(Request $request)
    {
        // រក្សាទុកកូដ store របស់អ្នកនៅទីនេះដដែល...
        // (ដើម្បីកុំអោយវែងពេក ខ្ញុំសុំមិនសរសេរឡើងវិញទេ ព្រោះមិនមានការកែប្រែនៅត្រង់នេះ)
         $request->validate([
            'table_id' => 'required|exists:tables,id',
            'items'    => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
        ]);

        // ... (Logic ដដែលរបស់អ្នក) ...
        
        return DB::transaction(function () use ($request) {
            $order = Order::where('table_id', $request->table_id)
                          ->where('status', 'pending')
                          ->first();

            if (!$order) {
                $order = Order::create([
                    'invoice_number' => 'INV-' . time() . '-' . $request->table_id,
                    'table_id'       => $request->table_id,
                    'user_id'        => Auth::id(),
                    'status'         => 'pending',
                    'total_amount'   => 0,
                ]);
                Table::where('id', $request->table_id)->update(['status' => 'busy']);
            }

            foreach ($request->items as $itemData) {
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
            
            // Recalculate Total immediately
            $this->recalculateOrderTotal($order->id);

            return response()->json([
                'status'  => 'success',
                'message' => 'Order placed successfully!',
                'order_id' => $order->id
            ]);
        });
    }

    // 🔥 FUNCTION ថ្មីសម្រាប់កែចំនួន ឬលុបមុខម្ហូប
    public function updateItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'action'  => 'required|in:increase,decrease,remove',
        ]);

        return DB::transaction(function () use ($request) {
            $item = OrderItem::with('addons')->findOrFail($request->item_id);
            
            if ($request->action === 'remove') {
                // លុបចោលទាំងស្រុង (Addons នឹងលុបតាមរយៈ Cascade ឬលុបដៃ)
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
                    // បើចំនួននៅសល់ ១ ហើយចុចដក គឺលុបចោលតែម្តង
                    OrderItemAddon::where('order_item_id', $item->id)->delete();
                    $item->delete();
                }
            }

            // គណនាតម្លៃសរុបឡើងវិញភ្លាមៗ
            $newTotal = $this->recalculateOrderTotal($item->order_id);

            // ពិនិត្យមើលថាបើអស់ម្ហូបពី Order ត្រូវ update table ទៅ available វិញឬអត់ (Optional)
            $remainingItems = OrderItem::where('order_id', $item->order_id)->count();
            if ($remainingItems == 0) {
                 // អាចនឹង update status order ទៅ cancel ឬទុកដដែល
            }

            return response()->json([
                'status' => 'success',
                'total'  => $newTotal
            ]);
        });
    }

    

    public function checkout(Request $request)
    {
        $request->validate([
            'order_id'       => 'required|exists:orders,id',
            'received_amount'=> 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qr,card',
            'items'          => 'required|array', // ត្រូវការ items ដើម្បី update ចុងក្រោយ
        ]);

        return DB::transaction(function () use ($request) {
            $order = Order::with('items.addons')->findOrFail($request->order_id);

            if ($order->status == 'completed') {
                return response()->json(['status' => 'error', 'message' => 'Order is already paid!'], 400);
            }

            // =========================================================
            // ជំហានទី ១: SYNC ITEMS (Update តាមអ្វីដែល user កែលើ​ screen)
            // =========================================================
            
            // 1. ប្រមូល ID របស់ Items ដែល client ផ្ញើមក
            $submittedItemIds = collect($request->items)->pluck('id')->filter()->toArray();

            // 2. លុប Items ណាដែលក្នុង DB មាន តែ Client អត់មាន (មានន័យថាគេលុបចោល)
            OrderItem::where('order_id', $order->id)
                     ->whereNotIn('id', $submittedItemIds)
                     ->delete(); 

            // 3. Update ចំនួន Items និង Addons ដែលនៅសល់
            foreach ($request->items as $itemData) {
                $item = OrderItem::find($itemData['id']);
                if ($item) {
                    $item->update(['quantity' => $itemData['quantity']]);

                    if (!empty($itemData['addons'])) {
                        $submittedAddonIds = collect($itemData['addons'])->pluck('id')->toArray();
                        
                        // លុប Addon ចោល
                        OrderItemAddon::where('order_item_id', $item->id)
                                      ->whereNotIn('id', $submittedAddonIds)
                                      ->delete();

                        // Update Addon Qty
                        foreach ($itemData['addons'] as $addonData) {
                            OrderItemAddon::where('id', $addonData['id'])
                                          ->update(['quantity' => $addonData['quantity']]);
                        }
                    } else {
                        // លុប Addons ទាំងអស់បើគេដកអស់
                        $item->addons()->delete();
                    }
                }
            }

            // =========================================================
            // ជំហានទី ២: PROCESS PAYMENT
            // =========================================================

            // គណនាលុយឡើងវិញ (Server Side Calculation)
            $totalAmount = $this->recalculateOrderTotal($order->id);
            
            $change = $request->received_amount - $totalAmount;

            // ផ្ទៀងផ្ទាត់លុយ
            if ($request->payment_method == 'cash' && round($change, 2) < 0) {
                DB::rollBack(); 
                return response()->json(['status' => 'error', 'message' => 'Not enough cash received!'], 422);
            }

            // Update Order
            $order->update([
                'status'          => 'completed',
                'total_amount'    => $totalAmount,
                'payment_method'  => $request->payment_method,
                'received_amount' => $request->received_amount,
                'change_amount'   => $change,
                'paid_at'         => now(),
            ]);

            // ទំនេរតុ
            if ($order->table_id) {
                Table::where('id', $order->table_id)->update(['status' => 'available']);
            }

            return response()->json([
                'status'   => 'success',
                'message'  => 'Payment successful!',
                'order_id' => $order->id,
                'change'   => $change,
            ]);
        });
    }

    // Helper Function
    private function recalculateOrderTotal($orderId)
    {
        $order = Order::with(['items.addons'])->find($orderId);
        $totalAmount = 0;

        foreach ($order->items as $item) {
            $itemTotal = $item->price * $item->quantity;
            $addonTotal = 0;
            foreach ($item->addons as $addon) {
                $addonTotal += ($addon->price * ($addon->quantity ?? 1));
            }
            $totalAmount += ($itemTotal + $addonTotal);
        }

        $order->update(['total_amount' => $totalAmount]);
        return $totalAmount;
    }

    public function updateAddon(Request $request)
    {
        $request->validate([
            'addon_row_id' => 'required|exists:order_item_addons,id', // ID របស់បន្ទាត់ Addon
            'action'       => 'required|in:increase,decrease,remove',
        ]);

        return DB::transaction(function () use ($request) {
            $addon = OrderItemAddon::findOrFail($request->addon_row_id);
            
            // ទាញយក Order Item ដើម្បីដឹងថាវាជារបស់ Order ណា (សម្រាប់គណនាលុយសរុប)
            $orderItem = OrderItem::find($addon->order_item_id);

            if ($request->action === 'remove') {
                $addon->delete();
            } 
            elseif ($request->action === 'increase') {
                $addon->increment('quantity');
            } 
            elseif ($request->action === 'decrease') {
                if ($addon->quantity > 1) {
                    $addon->decrement('quantity');
                } else {
                    // បើនៅសល់ 1 ហើយចុចដក គឺលុបចោល
                    $addon->delete();
                }
            }

            // គណនាលុយសរុបឡើងវិញ (Function នេះមានស្រាប់ពីកូដមុន)
            $newTotal = $this->recalculateOrderTotal($orderItem->order_id);

            return response()->json([
                'status' => 'success',
                'total'  => $newTotal
            ]);
        });
    }
}