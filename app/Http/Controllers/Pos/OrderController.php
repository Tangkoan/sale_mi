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

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'table_id' => 'required|exists:tables,id',
            'items'    => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            // 2. ឆែកមើលថាតើតុនេះមាន Order ចាស់ដែលមិនទាន់គិតលុយទេ?
            $order = Order::where('table_id', $request->table_id)
                          ->where('status', 'pending')
                          ->first();

            // បើអត់មាន -> បង្កើត Order ថ្មី (Invoice)
            if (!$order) {
                $order = Order::create([
                    'invoice_number' => 'INV-' . time() . '-' . $request->table_id,
                    'table_id'       => $request->table_id,
                    'user_id'        => Auth::id(),
                    'status'         => 'pending',
                    'total_amount'   => 0,
                ]);

                // Update Table Status -> Busy
                Table::where('id', $request->table_id)->update(['status' => 'busy']);
            }

            // 3. បញ្ចូលមុខម្ហូប (Order Items)
            foreach ($request->items as $itemData) {
                // បង្កើត Order Item
                $orderItem = OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $itemData['product_id'],
                    'quantity'   => $itemData['qty'],
                    'price'      => $itemData['price'],
                    'note'       => $itemData['note'] ?? null,
                    'is_printed' => false,
                    'created_by' => Auth::id(),
                ]);

                // បញ្ចូល Addons (កែសម្រួលត្រង់នេះ)
                if (!empty($itemData['addons'])) {
                    foreach ($itemData['addons'] as $addon) {
                        OrderItemAddon::create([
                            'order_item_id' => $orderItem->id,
                            'addon_id'      => $addon['id'],
                            'price'         => $addon['price'],
                            'quantity'      => $addon['qty'] ?? 1 // <--- យក Qty ពី Frontend (សំខាន់!)
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