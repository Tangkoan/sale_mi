<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\KitchenDestination; // ✅ Import Model ថ្មី
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        // ✅ យក Destinations ទាំងអស់មកប្រើក្នុង Dropdown
        $destinations = KitchenDestination::select('id', 'name')->get();
        return view('admin.category.category_list', compact('destinations'));
    }

    public function fetchCategories(Request $request)
    {
        // ✅ កែសម្រួល៖ Eager Load 'destination' relationship
        // ដើម្បីឱ្យ Frontend អាចហៅ item.destination.name បាន
        $query = Category::with('destination'); 

        // 1. Search Keyword
        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }
        
        // 2. Filter Destination (បើមាន)
        if ($request->destination) {
            // Filter តាម relationship ឬ column ID ផ្ទាល់
            // បើ $request->destination គឺជា ID (លេខ):
            $query->where('kitchen_destination_id', $request->destination);
        }

        // 3. Handle Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        
        // បើ Sort តាម destination name (មិនមែន ID) អាចនឹងត្រូវការ join table
        // ប៉ុន្តែសម្រាប់ពេលនេះ sort តាម ID ឬ column ធម្មតាសិន
        $query->orderBy($sortBy, $sortDir);

        // 4. Pagination
        $perPage = $request->input('per_page', 10);
        
        $categories = ($perPage === 'all') 
            ? $query->paginate(999999) 
            : $query->paginate((int)$perPage);

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                   => 'required|string|max:255',
            // ✅ Validate foreign key
            'kitchen_destination_id' => 'required|exists:kitchen_destinations,id', 
            'image'                  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'required' => __('messages.field_required'),
            'image'    => __('messages.invalid_image'),
            'exists'   => __('messages.invalid_data'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => __('messages.invalid_data'),
                'errors'  => $validator->errors()
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            $data = [
                'name'                   => $request->name,
                'kitchen_destination_id' => $request->kitchen_destination_id, // ✅ Save ID
            ];

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('categories', 'public');
            }

            $category = Category::create($data);

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

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'                   => 'required|string|max:255',
            'kitchen_destination_id' => 'required|exists:kitchen_destinations,id',
            'image'                  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
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
            $category->kitchen_destination_id = $request->kitchen_destination_id; // ✅ Update ID

            if ($request->hasFile('image')) {
                if ($category->image && Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }
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

    // ... Destroy & BulkDelete នៅដដែល ...
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
        return response()->json(['status' => 'success', 'message' => __('messages.category_deleted')]);
    }

    public function bulkDelete(Request $request)
    {
        $categories = Category::whereIn('id', $request->ids)->get();
        $count = 0;
        foreach ($categories as $category) {
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $category->delete();
            $count++;
        }
        return response()->json(['status' => 'success', 'message' => __('messages.bulk_delete_success', ['count' => $count])]);
    }
}