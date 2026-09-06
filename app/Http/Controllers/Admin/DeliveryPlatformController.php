<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryPlatform;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DeliveryPlatformController extends Controller
{
    public function index()
    {
        return view('admin.delivery_platform.index');
    }

    public function fetchPlatforms(Request $request)
    {
        $query = DeliveryPlatform::query();

        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = $request->input('per_page', 10);
        $platforms = ($perPage === 'all') 
            ? $query->paginate(999999) 
            : $query->paginate((int)$perPage);

        return response()->json($platforms);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048', // មិនលើស 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'ទិន្នន័យមិនត្រឹមត្រូវ', 'errors' => $validator->errors()], 422);
        }

        $data = [
            'name'   => $request->name,
            'status' => 'active',
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('delivery_platforms', 'public');
        }

        DeliveryPlatform::create($data);

        return response()->json(['status' => 'success', 'message' => 'បានបង្កើត Platform ថ្មីជោគជ័យ!']);
    }

    public function update(Request $request, $id)
    {
        $platform = DeliveryPlatform::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'ទិន្នន័យមិនត្រឹមត្រូវ', 'errors' => $validator->errors()], 422);
        }

        $platform->name = $request->name;

        if ($request->hasFile('logo')) {
            if ($platform->logo && Storage::disk('public')->exists($platform->logo)) {
                Storage::disk('public')->delete($platform->logo);
            }
            $platform->logo = $request->file('logo')->store('delivery_platforms', 'public');
        }

        $platform->save();

        return response()->json(['status' => 'success', 'message' => 'បានកែប្រែ Platform ជោគជ័យ!']);
    }

    public function destroy($id)
    {
        $platform = DeliveryPlatform::findOrFail($id);

        if ($platform->logo && Storage::disk('public')->exists($platform->logo)) {
            Storage::disk('public')->delete($platform->logo);
        }
        
        $platform->delete();
        
        return response()->json(['status' => 'success', 'message' => 'បានលុប Platform ជោគជ័យ!']);
    }

    public function toggleStatus($id)
    {
        $platform = DeliveryPlatform::findOrFail($id);
        $platform->status = $platform->status === 'active' ? 'inactive' : 'active';
        $platform->save();
        
        return response()->json(['status' => 'success', 'message' => 'ស្ថានភាពត្រូវបានកែប្រែជោគជ័យ']);
    }
}