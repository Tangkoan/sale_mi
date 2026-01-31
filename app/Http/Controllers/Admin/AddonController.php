<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Addon;
use App\Models\KitchenDestination; // ✅ Import Model
use Illuminate\Support\Facades\Validator;

class AddonController extends Controller
{
    public function index()
    {
        // ✅ យក Destinations ទាំងអស់ដើម្បីដាក់ក្នុង Dropdown ពេលបង្កើត Addon
        $destinations = KitchenDestination::where('is_active', true)->select('id', 'name')->get();
        return view('admin.addon.addon_list', compact('destinations'));
    }

    public function fetchAddons(Request $request)
    {
        // ✅ Eager Load Relationship ឈ្មោះ 'destination' (ត្រូវប្រាកដថា Model Addon មាន function destination())
        $query = Addon::with('destination'); 

        // Sort តាម Active (Active នៅលើគេ)
        $query->orderBy('is_active', 'desc');

        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // ✅ Filter តាម kitchen_destination_id (ជំនួស type)
        if ($request->kitchen_destination_id) {
            $query->where('kitchen_destination_id', $request->kitchen_destination_id);
        }

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        
        // ប្រសិនបើ Sort តាម destination, យើងតម្រៀបតាម ID ជំនួសសិន (Simple Sort)
        if ($sortBy === 'destination') {
            $query->orderBy('kitchen_destination_id', $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $perPage = $request->input('per_page', 10);
        $addons = ($perPage === 'all') 
            ? $query->paginate(999999) 
            : $query->paginate((int)$perPage);

        return response()->json($addons);
    }

    public function toggleStatus($id)
    {
        $addon = Addon::findOrFail($id);
        $addon->is_active = !$addon->is_active;
        $addon->save();
        return response()->json(['status' => 'success', 'message' => 'Addon status updated']);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                   => 'required|string|max:255',
            'price'                  => 'required|numeric|min:0',
            // ✅ Validate ID ពី Table kitchen_destinations
            'kitchen_destination_id' => 'required|exists:kitchen_destinations,id', 
        ], [
            'required' => __('messages.field_required'),
            'numeric'  => __('messages.invalid_number'),
            'exists'   => __('messages.invalid_data'),
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => __('messages.invalid_data'), 'errors' => $validator->errors()], 422);
        }

        $addon = Addon::create([
            'name'                   => $request->name,
            'price'                  => $request->price,
            'kitchen_destination_id' => $request->kitchen_destination_id, // ✅ Save ID
            'is_active'              => true
        ]);

        if(function_exists('activity')) {
            activity()->causedBy(auth()->user())->performedOn($addon)->log('created addon');
        }

        return response()->json(['status' => 'success', 'message' => __('messages.addon_created')]);
    }

    public function update(Request $request, $id)
    {
        $addon = Addon::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'                   => 'required|string|max:255',
            'price'                  => 'required|numeric|min:0',
            // ✅ Validate ID
            'kitchen_destination_id' => 'required|exists:kitchen_destinations,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => __('messages.invalid_data'), 'errors' => $validator->errors()], 422);
        }

        $addon->update([
            'name'                   => $request->name,
            'price'                  => $request->price,
            'kitchen_destination_id' => $request->kitchen_destination_id, // ✅ Update ID
        ]);

        if(function_exists('activity')) {
            activity()->causedBy(auth()->user())->performedOn($addon)->log('updated addon');
        }

        return response()->json(['status' => 'success', 'message' => __('messages.addon_updated')]);
    }

    public function destroy($id)
    {
        $addon = Addon::findOrFail($id);
        $addon->delete();
        return response()->json(['status' => 'success', 'message' => __('messages.addon_deleted')]);
    }

    public function bulkDelete(Request $request)
    {
        Addon::whereIn('id', $request->ids)->delete();
        return response()->json(['status' => 'success', 'message' => __('messages.bulk_delete_success', ['count' => count($request->ids)])]);
    }
}