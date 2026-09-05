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
        $categories = Category::select('id', 'name', 'kitchen_destination_id')->orderBy('name', 'asc')->get();
        
        $currentOrder = Order::where('table_id', $table_id)->where('status', 'pending')->first();

        // មិនបាច់បញ្ជូន products និង addons ទៅទៀតទេ ទុកឲ្យទំព័រស្រាលបំផុត!
        return view('pos.menu', compact('table', 'categories', 'currentOrder'));
    }

    // កែប្រែ Function ចាស់អោយស្រាល ដោយលុបការទាញ Products ចេញ
    public function fetchMenuData()
    {
        // ទុកតែ Addon ព្រោះ Product យើងទាញតាម Pagination វិញ
        $addons = Addon::select('id', 'name', 'price', 'is_active', 'kitchen_destination_id')->get();

        return response()->json([
            'addons' => $addons
        ]);
    }

    // បន្ថែម Function ថ្មីនេះសម្រាប់ Infinite Scroll
    public function fetchProductsPaginated(Request $request)
    {
        $query = Product::select('id', 'name', 'price', 'image', 'category_id', 'is_active', 'station_type')
                        ->where('name', 'not like', '%extra%')
                        ->with(['addons']);

        // បើមានរើស Category
        if ($request->category_id &&$request->category_id !== 'all') {
            $query->where('category_id',$request->category_id);
        }

        // បើមាន Search
        if ($request->search) {
            $query->where('name', 'like', '\%' .$request->search . '%');
        }

        // Pagination ម្ដង 10
        $products =$query->paginate(10);

        return response()->json($products);
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