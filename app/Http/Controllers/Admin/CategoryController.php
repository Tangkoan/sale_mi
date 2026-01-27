<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage; // ចាំបាច់សម្រាប់លុបរូបភាព
use Illuminate\Support\Facades\DB;       // ចាំបាច់សម្រាប់ Transaction

class CategoryController extends Controller
{
    // ==========================================
    // 1. VIEW (បង្ហាញទំព័រដើម)
    // ==========================================
    public function index()
    {
        return view('admin.category.category_list');
    }

    // ==========================================
    // 2. FETCH DATA (សម្រាប់ AJAX Table)
    // ==========================================
    public function fetchCategories(Request $request)
    {
        $query = Category::query();

        // Search Keyword
        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }
        
        // Filter by Type (Optional)
        if ($request->type) {
            $query->where('type', $request->type);
        }

        $perPage = $request->input('per_page', 10);
        
        // Return JSON
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
            'name'  => 'required|string|max:255',
            'type'  => 'required|in:food,drink',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'required' => __('messages.field_required'),
            'image'    => __('messages.invalid_image'),
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
                'name' => $request->name,
                'type' => $request->type,
            ];

            // Handle Image Upload
            if ($request->hasFile('image')) {
                // Save ចូល folder 'storage/app/public/categories'
                $data['image'] = $request->file('image')->store('categories', 'public');
            }

            $category = Category::create($data);

            // Log activity (ប្រសិនបើមាន package spatie/laravel-activitylog)
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
            'name'  => 'required|string|max:255',
            'type'  => 'required|in:food,drink',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
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
            $category->type = $request->type;

            // Check if new image is uploaded
            if ($request->hasFile('image')) {
                // 1. លុបរូបចាស់ចេញពី Storage
                if ($category->image && Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }
                // 2. ដាក់រូបថ្មីចូល
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
    // 5. DESTROY (លុបមួយ)
    // ==========================================
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // 1. លុបរូបភាព
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        // 2. លុប Record
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
    // 6. BULK DELETE (លុបច្រើនក្នុងពេលតែមួយ)
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

        // ទាញយកទិន្នន័យមកសិន ដើម្បី Loop លុបរូបភាព
        $categories = Category::whereIn('id', $request->ids)->get();
        $count = 0;

        foreach ($categories as $category) {
            // 1. លុបរូបភាពចេញពី Storage សម្រាប់ item នីមួយៗ
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            
            // 2. លុប Record
            $category->delete();
            $count++;
        }

        return response()->json([
            'status'  => 'success',
            'message' => __('messages.bulk_delete_success', ['count' => $count])
        ]);
    }
}