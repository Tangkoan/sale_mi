<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Addon;
use Illuminate\Support\Facades\Validator;

class AddonController extends Controller
{
    public function index()
    {
        return view('admin.addon.addon_list');
    }

    public function fetchAddons(Request $request)
    {
        $query = Addon::query();

        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // Filter តាម Type (kitchen/bar)
        if ($request->type) {
            $query->where('type', $request->type);
        }

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = $request->input('per_page', 10);
        $addons = ($perPage === 'all') 
            ? $query->paginate(999999) 
            : $query->paginate((int)$perPage);

        return response()->json($addons);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            // ✅ កែត្រង់នេះ៖ ទទួលយកតែ kitchen ឬ bar
            'type'  => 'required|in:kitchen,bar', 
        ], [
            'required' => __('messages.field_required'),
            'numeric'  => __('messages.invalid_number'),
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => __('messages.invalid_data'), 'errors' => $validator->errors()], 422);
        }

        $addon = Addon::create([
            'name'  => $request->name,
            'price' => $request->price,
            'type'  => $request->type, // kitchen ឬ bar
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
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            // ✅ កែត្រង់នេះ៖ ទទួលយកតែ kitchen ឬ bar
            'type'  => 'required|in:kitchen,bar',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => __('messages.invalid_data'), 'errors' => $validator->errors()], 422);
        }

        $addon->update([
            'name'  => $request->name,
            'price' => $request->price,
            'type'  => $request->type,
        ]);

        if(function_exists('activity')) {
            activity()->causedBy(auth()->user())->performedOn($addon)->log('updated addon');
        }

        return response()->json(['status' => 'success', 'message' => __('messages.addon_updated')]);
    }

    // ... (destroy និង bulkDelete រក្សាទុកដដែល មិនបាច់កែ) ...
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