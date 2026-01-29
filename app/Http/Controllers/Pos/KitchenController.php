<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class KitchenController extends Controller
{
    // 1. បង្ហាញទំព័រ Kitchen Screen (Blade View)
    public function index()
    {
        return view('pos.kitchen.index'); // យើងនឹងបង្កើត View នេះនៅជំហានក្រោយ
    }

    // 2. API: ទាញយក Order សម្រាប់ផ្ទះបាយ ឬ បារ (Real-time polling)
    public function fetchOrders(Request $request)
    {
        $destination = $request->query('destination', 'kitchen');

        // Logic កែតម្រូវ៖ ទាញយក Order ទាំងអស់ (ទោះគិតលុយហើយក៏ដោយ)
        // ឱ្យតែមានមុខម្ហូបដែលត្រូវនឹង Destination និងមិនទាន់ធ្វើរួច
        $orders = Order::with(['table', 'items' => function ($query) use ($destination) {
                $query->whereHas('product.category', function ($q) use ($destination) {
                    $q->where('destination', $destination);
                })
                ->whereIn('status', ['pending', 'cooking'])
                ->with(['product', 'addons.addon']);
            }])
            ->whereHas('items', function ($query) use ($destination) {
                // យកតែ Order ណាដែលនៅសល់ម្ហូបមិនទាន់ធ្វើ
                $query->whereHas('product.category', function ($q) use ($destination) {
                    $q->where('destination', $destination);
                })
                ->whereIn('status', ['pending', 'cooking']);
            })
            ->whereDate('created_at', today()) // បន្ថែម៖ យកតែ Order ថ្ងៃនេះ (ការពារកុំអោយ Order ចាស់ៗលោតមក)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($orders);
    }

    // 3. API: ចុងភៅចុច "Done" លើមុខម្ហូបមួយមុខៗ
    public function updateItemStatus(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'status'  => 'required|in:pending,cooking,ready,served'
        ]);

        $item = OrderItem::findOrFail($request->item_id);
        $item->status = $request->status;
        $item->save();

        return response()->json(['message' => 'Item status updated', 'status' => $item->status]);
    }

    // 4. API: ចុងភៅចុច "Done All" (ធ្វើរួចមួយតុតែម្ដង)
    public function markOrderReady(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'destination' => 'required|in:kitchen,bar'
        ]);

        // Update គ្រប់ Item ក្នុង Order នោះ អោយទៅជា 'ready'
        // តែតម្រូវអោយត្រូវ destination ផង (កុំអោយប៉ះរបស់ Bar ពេល Kitchen ចុច)
        OrderItem::where('order_id', $request->order_id)
            ->whereHas('product.category', function($q) use ($request) {
                $q->where('destination', $request->destination);
            })
            ->update(['status' => 'ready']);

        return response()->json(['message' => 'All items marked as ready']);
    }
}