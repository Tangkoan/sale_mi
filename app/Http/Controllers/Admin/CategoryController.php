<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\KitchenDestination;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class CategoryController extends Controller
{
    public function index()
    {
        $destinations = KitchenDestination::select('id', 'name')
            ->where('name', 'NOT LIKE', '%Cashier%') 
            ->where('name', 'NOT LIKE', '%អ្នកគិតលុយ%')
            ->get();

        return view('admin.category.category_list', compact('destinations'));
    }

    public function fetchCategories(Request $request)
    {
        $query = Category::with('destination'); 

        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }
        
        if ($request->destination) {
            $query->where('kitchen_destination_id', $request->destination);
        }

        // ✅ ប្ដូរ default sort មកតាម sort field វិញ
        $sortBy = $request->input('sort_by', 'sort'); 
        $sortDir = $request->input('sort_dir', 'asc');
        
        $query->orderBy($sortBy, $sortDir);

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
            'kitchen_destination_id' => 'required|exists:kitchen_destinations,id',
            'sort'                   => 'nullable|integer', // ✅ បន្ថែម Validation សម្រាប់ sort
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
                'kitchen_destination_id' => $request->kitchen_destination_id,
                'sort'                   => $request->sort ?? 0, // ✅ បញ្ចូលទិន្នន័យ sort
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
            'sort'                   => 'nullable|integer', // ✅ បន្ថែម Validation សម្រាប់ sort
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
            $category->kitchen_destination_id = $request->kitchen_destination_id;
            $category->sort = $request->sort ?? 0; // ✅ ធ្វើបច្ចុប្បន្នភាពទិន្នន័យ sort

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

        // ✅ ឆែកមើលថាតើ Category នេះមាន Product កំពុងប្រើឬអត់
        $hasProducts = Product::where('category_id', $id)->exists();

        if ($hasProducts) {
            return response()->json([
                'status' => 'error',
                'message' => 'មិនអាចលុបបានទេ ព្រោះមានផលិតផលកំពុងប្រើប្រាស់ប្រភេទ (Category) នេះ!'
            ], 400);
        }

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
        $deletedCount = 0;
        $skippedCount = 0; // ✅ រាប់ចំនួន Category ដែលមិនអាចលុបបាន

        foreach ($categories as $category) {
            // ✅ ឆែកមើលថាតើ Category នេះមាន Product កំពុងប្រើឬអត់
            $hasProducts = Product::where('category_id', $category->id)->exists();

            if ($hasProducts) {
                $skippedCount++;
                continue; // រំលងមិនលុប Category នេះទេ
            }

            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            
            $category->delete();
            $deletedCount++;
        }

        // ប្រសិនបើជ្រើសរើសលុបច្រើន តែគ្មានមួយណាអាចលុបបានទាល់តែសោះ
        if ($deletedCount === 0 && $skippedCount > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'មិនអាចលុបទិន្នន័យដែលបានជ្រើសរើសបានទេ ព្រោះពួកវាមានជាប់ទិន្នន័យផលិតផល!'
            ], 400);
        }

        // បង្កើតសារជោគជ័យ
        $message = __('messages.bulk_delete_success', ['count' => $deletedCount]);
        
        // ប្រសិនបើមាន Category ខ្លះត្រូវបានលុប និងខ្លះទៀតមិនអាចលុបបាន
        if ($skippedCount > 0) {
            $message .= " (រំលង $skippedCount ព្រោះមានជាប់ទិន្នន័យផលិតផល)";
        }

        return response()->json([
            'status' => 'success', 
            'message' => $message
        ]);
    }

    
}