<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use Spatie\Permission\Models\Permission;
use App\Models\Permission; // <--- ដាក់អាថ្មីរបស់អ្នកចូល
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index()
    {
        return view('admin.permission.permission_list');
    }

    public function fetchPermissions(Request $request)
    {
        // បន្ថែម withCount('roles') ដើម្បីរាប់ចំនួន Role ដែលប្រើ Permission នេះ
        $query = Permission::withCount('roles');

        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        $perPage = $request->input('per_page', 10);
        $perPage = ($perPage == 'all') ? $query->count() : $perPage;

        $permissions = $query->latest()->paginate($perPage);

        return response()->json($permissions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name'
        ], [
            'name.unique' => __('messages.error_permission_taken'),
        ]);

        Permission::create(['name' => $request->name]);

        return response()->json(['message' => __('messages.success_permission_create')]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', Rule::unique('permissions', 'name')->ignore($id)]
        ], [
            'name.unique' => __('messages.error_permission_taken'),
        ]);

        $permission = Permission::findOrFail($id);
        $permission->update(['name' => $request->name]);

        return response()->json(['message' => __('messages.success_permission_update')]);
    }

    // --- កន្លែងកែប្រែសំខាន់ទី ១ (Single Delete) ---
    public function destroy($id)
    {
        // រាប់ចំនួន Role មុននឹងលុប
        $permission = Permission::withCount('roles')->findOrFail($id);

        if ($permission->roles_count > 0) {
            return response()->json([
                'message' => __('messages.error_permission_in_use', ['name' => $permission->name, 'count' => $permission->roles_count])
            ], 422);
        }

        $permission->delete();

        return response()->json(['message' => __('messages.success_permission_delete')]);
    }


    // --- កន្លែងកែប្រែសំខាន់ទី ២ (Bulk Delete) ---
    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        
        $ids = $request->ids;

        // ១. ស្វែងរក Permission ណាដែលមាន Role កំពុងប្រើ
        $permissionsInUse = Permission::whereIn('id', $ids)
                                      ->has('roles') // មាន Role ភ្ជាប់
                                      ->pluck('name')
                                      ->toArray();

        // ២. បើមាន Permission ណាមួយជាប់ Role, បដិសេធការលុបទាំងអស់
        if (!empty($permissionsInUse)) {
            $names = implode(', ', $permissionsInUse);
            return response()->json([
                'message' => __('messages.error_bulk_permission_in_use', ['names' => $names])
            ], 422);
        }

        // ៣. បើគ្មានជាប់ទេ លុបទាំងអស់
        Permission::whereIn('id', $ids)->delete();

        return response()->json(['message' => __('messages.success_bulk_permission_delete')]);
    }
}