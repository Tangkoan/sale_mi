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

    public function menu($table_id)
    {
        $table = Table::findOrFail($table_id);
        
        $categories = Category::with(['products' => function($q) {
            $q->where('is_active', true);
        }])->get();

        // 🔥 កែប្រែ៖ ត្រូវតែមាន ->with(['category', 'addons'])
        // ដើម្បីឱ្យ Frontend ទទួលបានទិន្នន័យ Addon ដែលបាន Link ក្នុង Admin
        $products = Product::where('is_active', true)
                            ->with(['category', 'addons']) 
                            ->get();
        
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
        // យកតែ id, is_active, និង price (ក្រែងលោមានការប្តូរតម្លៃភ្លាមៗដែរ)
        $products = Product::select('id', 'is_active', 'price')->get();
        return response()->json($products);
    }

    public function getAddonStatuses()
    {
        // Fetch id and is_active for all addons
        $addons = Addon::select('id', 'is_active', 'price')->get();
        return response()->json($addons);
    }
}