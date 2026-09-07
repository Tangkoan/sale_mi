<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Modifier;
use App\Models\ModifierGroup;
use Illuminate\Support\Facades\Validator;

class ModifierController extends Controller
{
    public function index()
    {
        // ទាញយកក្រុមទាំងអស់សម្រាប់បង្ហាញក្នុង Dropdown
        $groups = ModifierGroup::select('id', 'name')->where('is_active', true)->get();
        return view('admin.modifier.index', compact('groups'));
    }

    public function fetchModifiers(Request $request)
    {
        $query = Modifier::with('group');

        // Filter តាមឈ្មោះ
        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // Filter តាមក្រុម (Group)
        if ($request->group_id) {
            $query->where('modifier_group_id', $request->group_id);
        }

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = $request->input('per_page', 10);
        $modifiers = ($perPage === 'all') 
            ? $query->paginate(999999) 
            : $query->paginate((int)$perPage);

        return response()->json($modifiers);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'modifier_group_id' => 'required|exists:modifier_groups,id',
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'ទិន្នន័យមិនត្រឹមត្រូវ', 'errors' => $validator->errors()], 422);
        }

        Modifier::create([
            'modifier_group_id' => $request->modifier_group_id,
            'name' => $request->name,
            'price' => $request->price ?? 0,
            'is_active' => true,
        ]);

        return response()->json(['status' => 'success', 'message' => 'បានបង្កើតជម្រើសជោគជ័យ!']);
    }

    public function update(Request $request, $id)
    {
        $modifier = Modifier::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'modifier_group_id' => 'required|exists:modifier_groups,id',
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'ទិន្នន័យមិនត្រឹមត្រូវ', 'errors' => $validator->errors()], 422);
        }

        $modifier->update([
            'modifier_group_id' => $request->modifier_group_id,
            'name' => $request->name,
            'price' => $request->price ?? 0,
        ]);

        return response()->json(['status' => 'success', 'message' => 'បានកែប្រែជម្រើសជោគជ័យ!']);
    }

    public function destroy($id)
    {
        $modifier = Modifier::findOrFail($id);
        $modifier->delete();
        
        return response()->json(['status' => 'success', 'message' => 'បានលុបជោគជ័យ!']);
    }

    public function bulkDelete(Request $request)
    {
        $modifiers = Modifier::whereIn('id', $request->ids)->get();
        $deletedCount = 0;

        foreach ($modifiers as $modifier) {
            $modifier->delete();
            $deletedCount++;
        }

        return response()->json(['status' => 'success', 'message' => "បានលុបទិន្នន័យចំនួន $deletedCount ជោគជ័យ!"]);
    }

    public function toggleStatus($id)
    {
        $modifier = Modifier::findOrFail($id);
        $modifier->is_active = !$modifier->is_active;
        $modifier->save();
        
        return response()->json(['status' => 'success', 'message' => 'ស្ថានភាពត្រូវបានកែប្រែជោគជ័យ']);
    }
}