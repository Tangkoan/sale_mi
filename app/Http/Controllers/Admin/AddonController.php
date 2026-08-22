<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Addon;
use App\Models\KitchenDestination;
use Illuminate\Support\Facades\Validator;

class AddonController extends Controller
{
    public function index()
    {
        $destinations = KitchenDestination::where('is_active', true)
            ->where('name', '!=', 'អ្នកគិតលុយ')
            ->select('id', 'name')
            ->get();
        return view('admin.addon.addon_list', compact('destinations'));
    }

    public function fetchAddons(Request $request)
    {
        $query = Addon::with('destination'); 

        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        if ($request->kitchen_destination_id) {
            $query->where('kitchen_destination_id', $request->kitchen_destination_id);
        }

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        
        if ($sortBy === 'destination') {
            $query->orderBy('kitchen_destination_id', $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $perPage = $request->input('per_page', 10);
        $addons = ($perPage === 'all') ? $query->paginate(999999) : $query->paginate((int)$perPage);

        return response()->json($addons);
    }

    public function toggleStatus($id)
    {
        $addon = Addon::findOrFail($id);
        $addon->is_active = !$addon->is_active;
        $addon->save();
        return response()->json(['status' => 'success', 'message' => 'Status updated']);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                   => 'required|string|max:255',
            'price'                  => 'required|numeric|min:0',
            'kitchen_destination_id' => 'required|exists:kitchen_destinations,id', 
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => __('messages.invalid_data'), 'errors' => $validator->errors()], 422);
        }

        $addon = Addon::create([
            'name'                   => $request->name,
            'price'                  => $request->price,
            'kitchen_destination_id' => $request->kitchen_destination_id,
            'is_active'              => true
        ]);

        return response()->json(['status' => 'success', 'message' => __('messages.addon_created')]);
    }

    public function update(Request $request, $id)
    {
        $addon = Addon::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'                   => 'required|string|max:255',
            'price'                  => 'required|numeric|min:0',
            'kitchen_destination_id' => 'required|exists:kitchen_destinations,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => __('messages.invalid_data'), 'errors' => $validator->errors()], 422);
        }

        $addon->update([
            'name'                   => $request->name,
            'price'                  => $request->price,
            'kitchen_destination_id' => $request->kitchen_destination_id,
        ]);

        return response()->json(['status' => 'success', 'message' => __('messages.addon_updated')]);
    }

    public function destroy($id)
    {
        $addon = Addon::findOrFail($id);

        if ($addon->products()->exists()) {
            return response()->json([
                'status' => 'error', 
                'message' => 'មិនអាចលុបបានទេ ព្រោះជម្រើសបន្ថែមនេះកំពុងត្រូវបានប្រើប្រាស់ជាមួយមុខម្ហូប។'
            ], 400); 
        }

        $addon->delete();
        return response()->json(['status' => 'success', 'message' => __('messages.addon_deleted')]);
    }

    public function bulkDelete(Request $request)
    {
        $addons = Addon::whereIn('id', $request->ids)->get();
        $deletedCount = 0;
        $failedCount = 0;

        foreach ($addons as $addon) {
            if ($addon->products()->exists()) {
                $failedCount++;
                continue; 
            }
            
            $addon->delete();
            $deletedCount++;
        }

        if ($failedCount > 0) {
            return response()->json([
                'status' => 'warning', 
                'message' => "បានលុបចំនួន $deletedCount និងបដិសេធ $failedCount (ព្រោះកំពុងប្រើជាមួយមុខម្ហូប)។"
            ]);
        }

        return response()->json(['status' => 'success', 'message' => __('messages.bulk_delete_success', ['count' => $deletedCount])]);
    }

    // 🌟 [បន្ថែមថ្មី] Function សម្រាប់ Duplicate 🌟
    public function duplicate($id)
    {
        try {
            $addon = Addon::findOrFail($id);
            
            $newAddon = $addon->replicate();
            $newAddon->name = $addon->name . ' (Copy)'; // បន្ថែមពាក្យ (Copy)
            $newAddon->is_active = false; // លាក់សិន ការពារកុំឲ្យលោតចូល Menu ភ្លាមៗ
            $newAddon->created_at = now();
            
            $newAddon->save();
            
            return response()->json(['status' => 'success', 'message' => 'ជម្រើសបន្ថែមត្រូវបាន Duplicate ដោយជោគជ័យ!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'មានបញ្ហាក្នុងការ Duplicate: ' . $e->getMessage()], 500);
        }
    }
}