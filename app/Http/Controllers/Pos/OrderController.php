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

use App\Models\Product; // <--- កុំភ្លេច use Model Product

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validation ធម្មតា
        $request->validate([
            'table_id' => 'required|exists:tables,id',
            'items'    => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
        ]);

        // 🔥 1. SMART CHECK (ប្រមូល List មុខម្ហូបដែលអស់)
        $outOfStockItems = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            
            // បើផលិតផលត្រូវបានបិទ (Inactive)
            if (!$product || !$product->is_active) {
                $outOfStockItems[] = [
                    'id' => $item['product_id'],
                    'name' => $product ? $product->name : 'Unknown Item'
                ];
            }
        }

        // ប្រសិនបើមានមុខម្ហូបអស់ស្តុក សូម្បីតែ ១ មុខ
        if (count($outOfStockItems) > 0) {
            return response()->json([
                'status' => 'out_of_stock', // ដាក់ Status ពិសេស
                'message' => 'មុខម្ហូបខ្លះបានអស់ពីស្តុក។ ប្រព័ន្ធនឹងលុបវាចេញពីការកុម្ម៉ង់។',
                'out_of_stock_items' => $outOfStockItems // ផ្ញើ ID ទៅឱ្យ Frontend
            ], 422);
        }

        
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
                // ... (Create Order Items Logic របស់អ្នកនៅដដែល) ...
                $orderItem = OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $itemData['product_id'],
                    'quantity'   => $itemData['qty'],
                    'price'      => $itemData['price'],
                    'note'       => $itemData['note'] ?? null,
                    'is_printed' => false,
                    'status'     => 'pending', // សំខាន់សម្រាប់ KDS
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

            return response()->json([
                'status'  => 'success',
                'message' => 'Order placed successfully!',
                'order_id' => $order->id
            ]);
        });
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:tables,id',
            'received_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qr,card',
        ]);

        return DB::transaction(function () use ($request) {
            $order = Order::where('table_id', $request->table_id)
                          ->where('status', 'pending')
                          ->firstOrFail();

            // គណនាតម្លៃសរុបឡើងវិញអោយច្បាស់ (ការពារ Frontend បន្លំ)
            $totalAmount = 0;
            foreach ($order->items as $item) {
                $itemTotal = $item->price * $item->quantity;
                $addonTotal = 0;
                // ត្រូវប្រាកដថាបាន load addons ក្នុង model OrderItem ឬ query យកមក
                foreach ($item->addons as $addon) {
                    $addonTotal += ($addon->price * ($addon->quantity ?? 1));
                }
                $totalAmount += ($itemTotal + $addonTotal);
            }

            $change = $request->received_amount - $totalAmount;

            if ($change < 0) {
                return response()->json(['message' => 'Not enough cash received!'], 422);
            }

            $order->update([
                'status' => 'completed',
                'total_amount' => $totalAmount, // Update total ចូល DB ផង
                'payment_method' => $request->payment_method,
                'received_amount' => $request->received_amount,
                'change_amount' => $change,
                'paid_at' => now(),
            ]);

            Table::where('id', $request->table_id)->update(['status' => 'available']);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment successful!',
                'change' => $change,
            ]);
        });
    }
}