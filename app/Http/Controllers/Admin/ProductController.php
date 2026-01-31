<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Addon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        // ✅ កែ៖ Eager Load 'destination' របស់ Category ដើម្បីយកឈ្មោះ (Wok, Bar...)
        // និង Select 'kitchen_destination_id' ដើម្បីផ្គូផ្គងជាមួយ Addon
        $categories = Category::with('destination')
            ->select('id', 'name', 'kitchen_destination_id') 
            ->get();

        // ✅ កែ៖ Select 'kitchen_destination_id' របស់ Addon
        $addons = Addon::select('id', 'name', 'price', 'kitchen_destination_id')
            ->where('is_active', true)
            ->get();

        return view('admin.product.product_list', compact('categories', 'addons'));
    }

    public function fetchProducts(Request $request)
    {
        // ✅ Eager Load Relationship សម្រាប់បង្ហាញក្នុង Table
        // category.destination: ដើម្បីបង្ហាញថា Product នេះនៅផ្នែកណា (តាម Category)
        $query = Product::with(['category.destination', 'addons']);

        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = $request->input('per_page', 10);
        $products = ($perPage === 'all') 
            ? $query->paginate(999999) 
            : $query->paginate((int)$perPage);

        return response()->json($products);
    }

    // ... (Store, Update, Delete, ToggleStatus រក្សាទុកដដែល មិនបាច់កែ) ...
    // ព្រោះ Product មិនមាន field kitchen_destination_id ទេ
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|image|max:2048',
            'addons'      => 'nullable|array',
            'addons.*'    => 'exists:addons,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => __('messages.invalid_data'), 'errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request) {
            $data = [
                'name'        => $request->name,
                'category_id' => $request->category_id,
                'price'       => $request->price,
                'is_active'   => true,
            ];

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($data);

            if ($request->has('addons')) {
                $product->addons()->sync($request->addons);
            }

            if(function_exists('activity')) {
                activity()->causedBy(auth()->user())->performedOn($product)->log('created product');
            }

            return response()->json(['status' => 'success', 'message' => __('messages.product_created')]);
        });
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|image|max:2048',
            'addons'      => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => __('messages.invalid_data'), 'errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request, $product) {
            $product->name        = $request->name;
            $product->category_id = $request->category_id;
            $product->price       = $request->price;

            if ($request->hasFile('image')) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $product->image = $request->file('image')->store('products', 'public');
            }

            $product->save();
            $product->addons()->sync($request->addons ?? []);

            if(function_exists('activity')) {
                activity()->causedBy(auth()->user())->performedOn($product)->log('updated product');
            }

            return response()->json(['status' => 'success', 'message' => __('messages.product_updated')]);
        });
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
        $product->addons()->detach();
        $product->delete();
        return response()->json(['status' => 'success', 'message' => __('messages.product_deleted')]);
    }

    public function bulkDelete(Request $request)
    {
        $products = Product::whereIn('id', $request->ids)->get();
        foreach ($products as $product) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $product->addons()->detach();
            $product->delete();
        }
        return response()->json(['status' => 'success', 'message' => __('messages.bulk_delete_success', ['count' => count($products)])]);
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->is_active = !$product->is_active;
        $product->save();
        return response()->json(['status' => 'success', 'message' => 'Status updated']);
    }
}