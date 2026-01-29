<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Table;
use App\Models\Order;
use App\Models\Category;
use App\Models\Product;
use App\Models\Addon;
use App\Models\ShopInfo;

class PosController extends Controller
{
    public function index()
    {
        return view('pos.tables');
    }

    public function fetchTables()
    {
        $tables = Table::orderBy('name', 'asc')->get();
        return response()->json($tables);
    }

    public function selectTable($id)
    {
        return redirect()->route('pos.menu', ['table_id' => $id]);
    }

    // =================================================================
    // 🔥 កន្លែងដែលត្រូវកែគឺនៅត្រង់នេះ (FUNCTION MENU)
    // =================================================================
    public function menu($table_id)
    {
        $table = Table::findOrFail($table_id);
        
        // ១. កែត្រង់នេះ៖ ដក query function ដែល filter active ចេញ
        // ពីមុន៖ Category::with(['products' => function($q) { $q->where('is_active', true); }])->get();
        // ឥឡូវ៖ យក Products ទាំងអស់ក្នុង Category ទោះ Active ឬអត់
        $categories = Category::with('products')->get();

        // ២. កែត្រង់នេះ៖ ដក where('is_active', true) ចេញ
        // ដើម្បីអោយ Frontend ទទួលបានទិន្នន័យទាំងអស់ រួចចាំអោយ JS គ្រប់គ្រងការបង្ហាញ (Inactive = Gray)
        $products = Product::with(['category', 'addons']) // ទុក with ដដែល
                           ->get(); // យកទាំងអស់
        
        $addons = Addon::all();

        $currentOrder = Order::where('table_id', $table_id)
                             ->where('status', 'pending')
                             ->first();

        return view('pos.menu', compact('table', 'categories', 'products', 'addons', 'currentOrder'));
    }

    public function getOrderDetails($table_id)
    {
        $order = Order::with(['items.product', 'items.addons.addon'])
                    ->where('table_id', $table_id)
                    ->where('status', 'pending')
                    ->first();

        if (!$order) {
            return response()->json(['error' => 'No active order found'], 404);
        }

        $grandTotal = 0;

        foreach ($order->items as $item) {
            $itemTotal = $item->price * $item->quantity;
            $addonTotal = 0;
            foreach ($item->addons as $addonItem) {
                $qty = $addonItem->quantity ?? 1; 
                $price = $addonItem->price;
                $addonTotal += ($price * $qty);
            }
            $grandTotal += ($itemTotal + $addonTotal);
        }

        $shop = ShopInfo::first();

        return response()->json([
            'order' => $order,
            'items' => $order->items,
            'total' => $grandTotal, 
            'invoice_number' => $order->invoice_number,
            'date' => $order->created_at->format('d/m/Y H:i'),
            'shop' => $shop
        ]);
    }

    // API: សម្រាប់អោយ Frontend ហៅឆែកមើលថាផលិតផលណាខ្លះ Active/Inactive
    public function getProductStatuses()
    {
        // កន្លែងនេះត្រូវហើយ គឺយកទាំងអស់ដើម្បី update status
        $products = Product::select('id', 'is_active', 'price')->get();
        return response()->json($products);
    }

    public function getAddonStatuses()
    {
        $addons = Addon::select('id', 'is_active', 'price')->get();
        return response()->json($addons);
    }
}