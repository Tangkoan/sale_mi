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

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductController extends Controller
{
    public function index()
    {
        $categories = Category::with('destination')->select('id', 'name', 'kitchen_destination_id')->get();
        $addons = Addon::select('id', 'name', 'price', 'kitchen_destination_id')->where('is_active', true)->get();
        
        // ✅ កែត្រង់នេះ៖ ថែម where('name', '!=', 'អ្នកគិតលុយ') ដើម្បីលាក់វាពី Dropdown
        $destinations = DB::table('kitchen_destinations')
            ->select('id', 'name')
            ->where('name', '!=', 'អ្នកគិតលុយ') // ដកអ្នកគិតលុយចេញ
            ->get();

        return view('admin.product.product_list', compact('categories', 'addons', 'destinations'));
    }

    // Function សម្រាប់ Duplicate Product
    public function duplicate($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                // 1. រក Product ចាស់និង Addons របស់វា
                $product = Product::with('addons')->findOrFail($id);
                
                // 2. ចំលងទិន្នន័យ (Replicate នឹងចំលងទិន្នន័យទាំងអស់ លើកលែងតែ ID និង Created_at)
                $newProduct = $product->replicate();
                $newProduct->name = $product->name . ' (Copy)'; // ថែមពាក្យ (Copy)
                $newProduct->is_active = false; // លាក់សិនពេល Duplicate រួច ការពារក្រែងចង់ប្ដូររូប ឬកែតម្លៃ មុនពេលបង្ហាញ
                $newProduct->created_at = now();
                
                // 3. ចំលង File រូបភាព
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    $extension = pathinfo($product->image, PATHINFO_EXTENSION);
                    $newImageName = 'products/dup_' . time() . '_' . uniqid() . '.' . $extension;
                    
                    // Copy File ទៅជា File ថ្មី
                    Storage::disk('public')->copy($product->image, $newImageName);
                    
                    // ផ្ដល់ឈ្មោះរូបភាពថ្មីទៅឲ្យ Product ថ្មី
                    $newProduct->image = $newImageName;
                }
                
                // 4. Save Product ថ្មីចូល Database
                $newProduct->save();
                
                // 5. ចំលង Addons (Relations)
                if ($product->addons->isNotEmpty()) {
                    $newProduct->addons()->sync($product->addons->pluck('id'));
                }
                
                // Log activity បើមានប្រើ
                if(function_exists('activity')) {
                    activity()->causedBy(auth()->user())->performedOn($newProduct)->log('duplicated product');
                }
                
                return response()->json(['status' => 'success', 'message' => 'ផលិតផលត្រូវបាន Duplicate ដោយជោគជ័យ!']);
            });
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'មានបញ្ហាក្នុងការ Duplicate: ' . $e->getMessage()], 500);
        }
    }

    public function fetchProducts(Request $request)
    {
        $query = Product::with(['category.destination', 'addons']);

        // 1. លក្ខខណ្ឌស្វែងរកតាមឈ្មោះ
        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // 2. លក្ខខណ្ឌ Filter តាមប្រភេទទំនិញ (Category)
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // ✅ 3. ត្រូវថែមលក្ខខណ្ឌនេះ ដើម្បី Filter តាមផ្នែកផ្ទះបាយ (Kitchen Destination)
        if ($request->destination_id) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('kitchen_destination_id', $request->destination_id);
            });
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
            // លុប max: ចេញ អនុញ្ញាតអោយ Upload រូបប៉ុណ្ណាក៏បានចូលមកដល់កូដ
            'image'       => 'nullable|image', 
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
                $file = $request->file('image');
                $fileSize = $file->getSize();

                // បើទំហំរូបភាពធំជាង 5MB (5242880 bytes) ធ្វើការ Resize ទោះធំប៉ុណ្ណាក៏ដោយ
                if ($fileSize > 5242880) {
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($file->getRealPath());
                    
                    // បង្រួម Width មកត្រឹម 1024px ដោយរក្សា Aspect Ratio
                    $image->scaleDown(width: 1024);
                    
                    $imageName = 'products/resized_' . time() . '_' . uniqid() . '.jpg';
                    Storage::disk('public')->put($imageName, (string) $image->toJpeg(80));
                    
                    $data['image'] = $imageName;
                } else {
                    // បើមិនលើសពី 5MB ទេ Save តាមធម្មតា
                    $data['image'] = $file->store('products', 'public');
                }
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
            // លុប max: ចេញ អនុញ្ញាតអោយ Upload រូបប៉ុណ្ណាក៏បានចូលមកដល់កូដ
            'image'       => 'nullable|image', 
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
                // លុបរូបចាស់ចោលសិន
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                $file = $request->file('image');
                $fileSize = $file->getSize();

                // បើទំហំរូបភាពធំជាង 5MB (5242880 bytes) ធ្វើការ Resize ទោះធំប៉ុណ្ណាក៏ដោយ
                if ($fileSize > 5242880) {
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($file->getRealPath());
                    
                    // បង្រួម Width មកត្រឹម 1024px ដោយរក្សា Aspect Ratio
                    $image->scaleDown(width: 1024);
                    
                    $imageName = 'products/resized_' . time() . '_' . uniqid() . '.jpg';
                    Storage::disk('public')->put($imageName, (string) $image->toJpeg(80));
                    
                    $product->image = $imageName;
                } else {
                    // បើមិនលើសពី 5MB ទេ Save តាមធម្មតា
                    $product->image = $file->store('products', 'public');
                }
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

        // ✅ ការពារមិនអោយលុប បើធ្លាប់មានអតិថិជនកម្ម៉ង់ (មានទិន្នន័យក្នុង order_items)
        if ($product->orderItems()->exists()) {
            return response()->json([
                'status' => 'error', 
                'message' => 'មិនអាចលុបបានទេ! ផលិតផលនេះធ្លាប់មានប្រវត្តិការបញ្ជាទិញរួចហើយ។'
            ], 400); // ប្រើ 400 Bad Request ដើម្បីឲ្យ Frontend ចាប់ Error នេះបាន
        }

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
        $deletedCount = 0;
        $failedCount = 0;

        foreach ($products as $product) {
            // ✅ បើធ្លាប់កម្ម៉ង់ រំលងមិនលុបវាទេ
            if ($product->orderItems()->exists()) {
                $failedCount++;
                continue; 
            }

            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $product->addons()->detach();
            $product->delete();
            
            $deletedCount++;
        }

        // ប្រាប់ User ពីលទ្ធផល
        if ($failedCount > 0) {
            return response()->json([
                'status' => 'warning', 
                'message' => "លុបបានជោគជ័យ $deletedCount, និងបដិសេធមិនអោយលុប $failedCount (ដោយសារធ្លាប់មានការកម្ម៉ង់ពីមុន)។"
            ]);
        }

        return response()->json(['status' => 'success', 'message' => __('messages.bulk_delete_success', ['count' => $deletedCount])]);
    }

    public function toggleStatus($id)
    {
        if (!auth()->user()->can('product-edit-status')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403);
        }

        $product = Product::findOrFail($id);
        $product->is_active = !$product->is_active;
        $product->save();
        
        return response()->json(['status' => 'success', 'message' => 'Product status updated successfully']);
    }
}