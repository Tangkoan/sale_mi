<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\KitchenDestination; // ✅ Import Model
use Illuminate\Support\Facades\DB;

class KitchenController extends Controller
{
    // 1. បង្ហាញទំព័រ Kitchen Screen
    public function index()
    {
        // ✅ ទាញយក Destination ទាំងអស់ដើម្បីបង្កើតប៊ូតុងនៅ Frontend
        $destinations = KitchenDestination::where('is_active', true)->get();
        
        return view('pos.kitchen.index', compact('destinations'));
    }

    // 2. API: ទាញយក Order
    public function fetchOrders(Request $request)
    {
        // ✅ ទទួលយក ID ជំនួសឱ្យ String
        $destinationId = $request->query('kitchen_destination_id');

        if (!$destinationId) {
            return response()->json([]);
        }

        // Logic: ទាញយក Order ដែលមាន Item ត្រូវនឹង Destination ID នេះ
        $orders = Order::with(['table', 'items' => function ($query) use ($destinationId) {
                // Filter Items យកតែរបស់ Destination នេះ
                $query->whereHas('product.category', function ($q) use ($destinationId) {
                    $q->where('kitchen_destination_id', $destinationId);
                })
                ->whereIn('status', ['pending', 'cooking'])
                ->with(['product', 'addons.addon']);
            }])
            ->whereHas('items', function ($query) use ($destinationId) {
                // Filter Order យកតែ Order ណាដែលមាន Item របស់ Destination នេះ
                $query->whereHas('product.category', function ($q) use ($destinationId) {
                    $q->where('kitchen_destination_id', $destinationId);
                })
                ->whereIn('status', ['pending', 'cooking']);
            })
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($orders);
    }

    // 3. API: Update Item Status (មិនបាច់កែព្រោះប្រើ Item ID)
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

    // 4. API: Done All (កែសម្រួលឱ្យប្រើ ID)
    public function markOrderReady(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'kitchen_destination_id' => 'required|exists:kitchen_destinations,id' // ✅ Validate ID
        ]);

        // Update គ្រប់ Item ក្នុង Order នោះ ដែលស្ថិតក្នុង Destination ID នេះ
        OrderItem::where('order_id', $request->order_id)
            ->whereHas('product.category', function($q) use ($request) {
                $q->where('kitchen_destination_id', $request->kitchen_destination_id);
            })
            ->update(['status' => 'ready']);

        return response()->json(['message' => 'All items marked as ready']);
    }
}