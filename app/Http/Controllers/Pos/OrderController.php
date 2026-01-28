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
                    'invoice_number' => 'INV-' . time() . '-' . $request->table_id, // លេខវិក្កយបត្របណ្តោះអាសន្ន
                    'table_id'       => $request->table_id,
                    'user_id'        => Auth::id(), // អ្នកបើកតុដំបូង
                    'status'         => 'pending',
                    'total_amount'   => 0, // នឹង Update តាមក្រោយ
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
                    'price'      => $itemData['price'], // តម្លៃលក់ជាក់ស្តែង
                    'note'       => $itemData['note'] ?? null,
                    'is_printed' => false, // សំខាន់សម្រាប់ Printer (ជំហានក្រោយ)
                    'created_by' => Auth::id(), // អ្នកចុចកុម្ម៉ង់មុខម្ហូបនេះ
                ]);

                // បញ្ចូល Addons (បើមាន)
                if (!empty($itemData['addons'])) {
                    foreach ($itemData['addons'] as $addon) {
                        OrderItemAddon::create([
                            'order_item_id' => $orderItem->id,
                            'addon_id'      => $addon['id'],
                            'price'         => $addon['price'],
                            'quantity'      => 1 // ជាទូទៅ Addon គិត ១
                        ]);
                    }
                }
            }

            // 4. គណនាតម្លៃសរុបឡើងវិញ (Optional: អាចធ្វើពេល Checkout ក៏បាន)
            // $this->recalculateTotal($order);

            // 5. Fire Event សម្រាប់ Printer (យើងនឹងធ្វើនៅជំហានបន្ទាប់)
            // event(new OrderCreated($order));

            return response()->json([
                'status'  => 'success',
                'message' => 'Order placed successfully!',
                'order_id' => $order->id
            ]);
        });
    }
}