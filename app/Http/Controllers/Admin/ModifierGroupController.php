<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ModifierGroup;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ModifierGroupController extends Controller
{
    public function index()
    {
        return view('admin.modifier_group.index');
    }

    public function fetchGroups(Request $request)
    {
        $query = ModifierGroup::query();

        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = $request->input('per_page', 10);
        $groups = ($perPage === 'all') 
            ? $query->paginate(999999) 
            : $query->paginate((int)$perPage);

        return response()->json($groups);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:single,multiple',
            'is_required' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'ទិន្នន័យមិនត្រឹមត្រូវ', 'errors' => $validator->errors()], 422);
        }

        $group = ModifierGroup::create([
            'name' => $request->name,
            'type' => $request->type,
            'is_required' => $request->is_required ?? false,
            'is_active' => true,
        ]);

        return response()->json(['status' => 'success', 'message' => 'បានបង្កើតក្រុមជម្រើសជោគជ័យ!']);
    }

    public function update(Request $request, $id)
    {
        $group = ModifierGroup::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:single,multiple',
            'is_required' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'ទិន្នន័យមិនត្រឹមត្រូវ', 'errors' => $validator->errors()], 422);
        }

        $group->update([
            'name' => $request->name,
            'type' => $request->type,
            'is_required' => $request->is_required ?? false,
        ]);

        return response()->json(['status' => 'success', 'message' => 'បានកែប្រែក្រុមជម្រើសជោគជ័យ!']);
    }

    public function destroy($id)
    {
        $group = ModifierGroup::findOrFail($id);

        // ✅ ការពារមិនឲ្យលុប ប្រសិនបើមានជម្រើសលម្អិត (Modifiers) នៅខាងក្នុង
        if ($group->modifiers()->exists()) {
            return response()->json([
                'status' => 'error', 
                'message' => 'មិនអាចលុបបានទេ! ក្រុមនេះមានជម្រើសលម្អិត (Modifiers) នៅខាងក្នុង។ សូមលុបជម្រើសលម្អិតចេញជាមុនសិន។'
            ], 400); // ប្រើ 400 Bad Request
        }

        $group->delete();
        
        return response()->json(['status' => 'success', 'message' => 'បានលុបជោគជ័យ!']);
    }

    public function bulkDelete(Request $request)
    {
        $groups = ModifierGroup::whereIn('id', $request->ids)->get();
        $deletedCount = 0;
        $failedCount = 0;

        foreach ($groups as $group) {
            // ✅ រំលងមិនលុប បើក្រុមនោះមាន Modifiers នៅខាងក្នុង
            if ($group->modifiers()->exists()) {
                $failedCount++;
                continue; 
            }

            $group->delete();
            $deletedCount++;
        }

        // ប្រាប់ User ពីលទ្ធផលបើមានទិន្នន័យខ្លះលុបមិនបាន
        if ($failedCount > 0) {
            return response()->json([
                'status' => 'warning', 
                'message' => "លុបបានជោគជ័យ $deletedCount, និងបដិសេធមិនលុប $failedCount (ដោយសារមានជម្រើសលម្អិតនៅខាងក្នុង)។"
            ]);
        }

        return response()->json(['status' => 'success', 'message' => "បានលុបទិន្នន័យចំនួន $deletedCount ជោគជ័យ!"]);
    }

    public function toggleStatus($id)
    {
        $group = ModifierGroup::findOrFail($id);
        $group->is_active = !$group->is_active;
        $group->save();
        
        return response()->json(['status' => 'success', 'message' => 'ស្ថានភាពត្រូវបានកែប្រែជោគជ័យ']);
    }
}