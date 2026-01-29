<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    // ==========================================
    // 1. VIEW
    // ==========================================
    public function index()
    {
        return view('admin.category.category_list');
    }

    // ==========================================
    // 2. FETCH DATA
    // ==========================================
    public function fetchCategories(Request $request)
    {
        $query = Category::query();

        // Search Keyword
        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }
        
        // Filter by Destination (កែពី type មក destination)
        if ($request->destination) {
            $query->where('destination', $request->destination);
        }

        $perPage = $request->input('per_page', 10);
        
        $categories = ($perPage === 'all') 
            ? $query->latest()->paginate(999999) 
            : $query->latest()->paginate((int)$perPage);

        return response()->json($categories);
    }

    // ==========================================
    // 3. STORE (បង្កើតថ្មី)
    // ==========================================
    public function store(Request $request)
    {
        // 1. Validation
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            // ✅ កែពី type => destination និងតម្លៃ validate ទៅជា kitchen,bar
            'destination' => 'required|in:kitchen,bar', 
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'required' => __('messages.field_required'),
            'image'    => __('messages.invalid_image'),
            'in'       => __('messages.invalid_data'), // បើគេ hack ដាក់តម្លៃផ្សេងក្រៅពី kitchen/bar
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => __('messages.invalid_data'),
                'errors'  => $validator->errors()
            ], 422);
        }

        // 2. Save Data logic
        return DB::transaction(function () use ($request) {
            $data = [
                'name'        => $request->name,
                'destination' => $request->destination, // ✅ Save ចូល column destination
                // ប្រសិនបើ table នៅមាន column 'type' ហើយមិនអាច null បាន
                // អ្នកអាចកំណត់ Default បាន (Optional)
                // 'type' => $request->destination == 'kitchen' ? 'food' : 'drink', 
            ];

            // Handle Image Upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('categories', 'public');
            }

            $category = Category::create($data);

            // Log activity
            if(function_exists('activity')) {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($category)
                    ->log('created category');
            }

            return response()->json([
                'status'  => 'success',
                'message' => __('messages.category_created'),
            ]);
        });
    }

    // ==========================================
    // 4. UPDATE (កែប្រែ)
    // ==========================================
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'destination' => 'required|in:kitchen,bar', // ✅ កែត្រង់នេះ
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => __('messages.invalid_data'),
                'errors'  => $validator->errors()
            ], 422);
        }

        return DB::transaction(function () use ($request, $category) {
            $category->name = $request->name;
            $category->destination = $request->destination; // ✅ កែត្រង់នេះ

            // Check if new image is uploaded
            if ($request->hasFile('image')) {
                // 1. លុបរូបចាស់
                if ($category->image && Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }
                // 2. ដាក់រូបថ្មី
                $category->image = $request->file('image')->store('categories', 'public');
            }

            $category->save();

            if(function_exists('activity')) {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($category)
                    ->log('updated category');
            }

            return response()->json([
                'status'  => 'success',
                'message' => __('messages.category_updated'),
            ]);
        });
    }

    // ==========================================
    // 5. DESTROY (លុបមួយ - មិនបាច់កែ)
    // ==========================================
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        if(function_exists('activity')) {
            activity()->causedBy(auth()->user())->performedOn($category)->log('deleted category');
        }

        return response()->json([
            'status'  => 'success',
            'message' => __('messages.category_deleted')
        ]);
    }

    // ==========================================
    // 6. BULK DELETE (លុបច្រើន - មិនបាច់កែ)
    // ==========================================
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array',
            'ids.*' => 'exists:categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => __('messages.invalid_data')], 422);
        }

        $categories = Category::whereIn('id', $request->ids)->get();
        $count = 0;

        foreach ($categories as $category) {
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $category->delete();
            $count++;
        }

        return response()->json([
            'status'  => 'success',
            'message' => __('messages.bulk_delete_success', ['count' => $count])
        ]);
    }
}