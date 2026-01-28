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
        // ផ្ញើទិន្នន័យ Category និង Addon ទៅ View ដើម្បីដាក់ក្នុង Modal Form
        $categories = Category::all();
        $addons = Addon::all();
        
        return view('admin.product.product_list', compact('categories', 'addons'));
    }

    public function fetchProducts(Request $request)
    {
        // Eager Load 'category' និង 'addons' ដើម្បីកុំឱ្យ Query យឺត
        $query = Product::with(['category', 'addons']);

        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // Filter by Category
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Sorting
        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        
        // Handle sorting logic safely
        if(in_array($sortBy, ['name', 'price', 'created_at', 'is_active'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest();
        }

        $perPage = $request->input('per_page', 10);
        $products = ($perPage === 'all') 
            ? $query->paginate(999999) 
            : $query->paginate((int)$perPage);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'addons'      => 'nullable|array',      // ទទួល Array នៃ Addon IDs
            'addons.*'    => 'exists:addons,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => __('messages.invalid_data'), 'errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request) {
            $data = [
                'name'        => $request->name,
                'category_id' => $request->category_id,
                'price'       => $request->price,
                'is_active'   => true, // Default active
            ];

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($data);

            // Sync Addons (Many-to-Many)
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
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'addons'      => 'nullable|array',
            'addons.*'    => 'exists:addons,id'
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

            // Update Addons Relationship
            // បើសិនជា user មិនបានជ្រើសរើស addon សោះ (checkbox ទំនេរ) យើងត្រូវ sync([]) ដើម្បីដក addon ចាស់ចេញ
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

        // Pivot table (product_addon) នឹងលុបស្វ័យប្រវត្តិប្រសិនបើអ្នកបានដាក់ onDelete('cascade') ក្នុង Migration
        // បើមិនដូច្នោះទេ ត្រូវដាក់ $product->addons()->detach(); នៅទីនេះ
        
        $product->delete();

        return response()->json(['status' => 'success', 'message' => __('messages.product_deleted')]);
    }

    public function bulkDelete(Request $request)
    {
        $products = Product::whereIn('id', $request->ids)->get();
        $count = 0;

        foreach ($products as $product) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();
            $count++;
        }

        return response()->json(['status' => 'success', 'message' => __('messages.bulk_delete_success', ['count' => $count])]);
    }

    // Toggle Active Status
    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->is_active = !$product->is_active;
        $product->save();
        return response()->json(['status' => 'success', 'message' => __('messages.status_updated')]);
    }
}