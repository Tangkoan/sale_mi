<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
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
        // (យើងអាចយកចំនួននេះទៅបង្ហាញនៅ Table ខាងមុខបានបើចង់)
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
            'name.unique' => 'This permission name has already been taken.',
        ]);

        Permission::create(['name' => $request->name]);

        return response()->json(['message' => 'Permission created successfully!']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', Rule::unique('permissions', 'name')->ignore($id)]
        ], [
            'name.unique' => 'This permission name has already been taken.',
        ]);

        $permission = Permission::findOrFail($id);
        $permission->update(['name' => $request->name]);

        return response()->json(['message' => 'Permission updated successfully!']);
    }

    // --- កន្លែងកែប្រែសំខាន់ទី ១ (Single Delete) ---
    public function destroy($id)
    {
        // រាប់ចំនួន Role មុននឹងលុប
        $permission = Permission::withCount('roles')->findOrFail($id);

        if ($permission->roles_count > 0) {
            return response()->json([
                'message' => "Cannot delete '{$permission->name}' because it is assigned to {$permission->roles_count} role(s)."
            ], 422);
        }

        $permission->delete();

        return response()->json(['message' => 'Permission deleted successfully!']);
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
                'message' => "Cannot delete selected permissions. The following are assigned to roles: {$names}."
            ], 422);
        }

        // ៣. បើគ្មានជាប់ទេ លុបទាំងអស់
        Permission::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Selected permissions deleted successfully!']);
    }
}