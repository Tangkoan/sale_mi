<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();
        return view('admin.role.role_list', compact('permissions'));
    }

    public function fetchRoles(Request $request)
    {
        // withCount('users') គឺសំខាន់ណាស់ ដើម្បីដឹងថា Role នឹងមាន User ប៉ុន្មាននាក់
        // វានឹងជួយបង្ហាញក្នុង Table និងងាយស្រួលពេល Frontend ចង់បង្ហាញ
        $query = Role::with('permissions')->withCount('users'); 

        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // Per Page អាចប្ដូរបានតាម request
        $perPage = $request->input('per_page', 10);
        $perPage = ($perPage == 'all') ? $query->count() : $perPage;

        $roles = $query->latest()->paginate($perPage);
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        // 1. Check Unique Name with Custom Message
        $request->validate([
            'name' => 'required|unique:roles,name'
        ], [
            'name.unique' => 'This role has already been taken! Please choose another name.',
        ]);

        Role::create(['name' => $request->name]);
        
        return response()->json(['message' => 'Role created successfully!']);
    }

    public function update(Request $request, $id)
    {
        // 1. Check Unique Name (ignore current ID)
        $request->validate([
            'name' => [
                'required', 
                Rule::unique('roles', 'name')->ignore($id)
            ]
        ], [
            'name.unique' => 'This role has already been taken! Please choose another name.',
        ]);

        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);

        return response()->json(['message' => 'Role updated successfully!']);
    }

    public function destroy($id)
    {
        $role = Role::withCount('users')->findOrFail($id);

        // 2. ការពារការលុប Role ដែលមាន User ប្រើ
        if ($role->users_count > 0) {
            return response()->json([
                'message' => "Cannot delete role '{$role->name}' because it has {$role->users_count} users assigned."
            ], 422); // 422 Unprocessable Entity
        }

        $role->delete();
        return response()->json(['message' => 'Role deleted successfully!']);
    }

    // 3. មុខងារ Bulk Delete (ថ្មី)
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        
        // រកមើល Roles ដែលមាន User ប្រើប្រាស់
        $rolesWithUsers = Role::whereIn('id', $ids)->has('users')->pluck('name')->toArray();

        // ប្រសិនបើមាន Role ណាមួយជាប់ User ឈប់ដំណើរការទាំងអស់ ហើយប្រាប់គេ
        if (!empty($rolesWithUsers)) {
            $names = implode(', ', $rolesWithUsers);
            return response()->json([
                'message' => "Cannot delete select roles. The following roles have users: {$names}."
            ], 422);
        }

        // បើគ្មានជាប់ User ទេ លុបទាំងអស់
        Role::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Selected roles deleted successfully!']);
    }

    // 4. មុខងារសម្រាប់ Permission Modal (Fetch Data)
    public function getRolePermissions($id)
    {
        $role = Role::findOrFail($id);
        // Return តែឈ្មោះ permission ដើម្បីឱ្យ Checkbox ក្នុង Vue/Alpine ស្គាល់
        $permissions = $role->permissions->pluck('name');
        return response()->json($permissions);
    }

    // 5. មុខងារ Update Permissions (Save Data)
    public function updateRolePermissions(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'array'
        ]);

        $role = Role::findOrFail($request->role_id);
        
        // syncPermissions នឹងលុបចាស់ចោល ហើយដាក់ថ្មីចូល (ល្អបំផុតសម្រាប់ Checkbox)
        $role->syncPermissions($request->permissions);

        return response()->json(['message' => 'Permissions assigned successfully!']);
    }
}