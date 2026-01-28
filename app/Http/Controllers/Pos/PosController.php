<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Table;
use App\Models\Order;
use App\Models\Category;
use App\Models\Product;
use App\Models\Addon;


class PosController extends Controller
{
    // 1. បង្ហាញទំព័ររើសតុ
    public function index()
    {
        return view('pos.tables');
    }

    // 2. API ទាញទិន្នន័យតុ (សម្រាប់ Auto Refresh)
    public function fetchTables()
    {
        // ទាញយកតុទាំងអស់ និងតម្រៀបតាមឈ្មោះ
        $tables = Table::orderBy('name', 'asc')->get();
        
        // យើងអាចបន្ថែម Logic ត្រង់នេះ បើចង់ដឹងថា Order ណាជារបស់តុណា
        // ប៉ុន្តែសម្រាប់ជំហាននេះ ត្រឹម Status គឺគ្រប់គ្រាន់
        
        return response()->json($tables);
    }

    // 3. មុខងារជ្រើសរើសតុ (Select Table)
    public function selectTable($id)
    {
        $table = Table::findOrFail($id);

        // Logic: 
        // - បើតុទំនេរ (Available) -> បង្កើត Order ថ្មី ឬគ្រាន់តែ Redirect ទៅ Menu
        // - បើតុរវល់ (Busy) -> Redirect ទៅ Order ចាស់របស់តុនោះ
        
        // សំរាប់ពេលនេះ យើងគ្រាន់តែ Redirect ទៅកាន់ទំព័រ Menu សិន
        // (យើងនឹងបង្កើត Route 'pos.menu' នៅជំហានបន្ទាប់)
        return redirect()->route('pos.menu', ['table_id' => $id]);
    }

    // 4. MENU SCREEN (ទំព័រកុម្ម៉ង់)
    public function menu($table_id)
    {
        $table = Table::findOrFail($table_id);
        
        // ទាញយក Categories ទាំងអស់ (Eager load products ដើម្បីស្រួលបង្ហាញ)
        // យើងទាញតែ Products ដែល Active ប៉ុណ្ណោះ
        $categories = Category::with(['products' => function($q) {
            $q->where('is_active', true);
        }])->get();

        // ទាញយក Products ទាំងអស់ (សម្រាប់ Search ក្រៅ Category)
        $products = Product::where('is_active', true)->with('category')->get();

        // ទាញយក Addons ទាំងអស់ (បែងចែកតាម Type: Food/Drink ក្នុង View)
        $addons = Addon::all();

        // ឆែកមើលថាតើតុនេះមាន Order ដែលកំពុងដំណើរការ (Pending) ឬអត់?
        // ប្រសិនបើមាន យើងនឹងបង្ហាញប៊ូតុង "មើលមុខម្ហូបដែលបានកម្មង់រួច" (ជំហានក្រោយ)
        $currentOrder = Order::where('table_id', $table_id)
                             ->where('status', 'pending')
                             ->first();

        return view('pos.menu', compact('table', 'categories', 'products', 'addons', 'currentOrder'));
    }
}