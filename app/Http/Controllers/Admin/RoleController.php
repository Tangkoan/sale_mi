<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
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
        $query = Role::with('permissions')->withCount('users'); 

        // ១. យក Level ខ្លួនឯង
        $myLevel = auth()->user()->roles->max('level') ?? 0;

        // ២. បើមិនមែន Super Admin (99) ទេ
        if ($myLevel < 99) { 
            $query->where('level', '<', $myLevel); 
        }

        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        $perPage = $request->input('per_page', 10);
        $perPage = ($perPage == 'all') ? $query->count() : $perPage;

        $roles = $query->latest()->paginate($perPage);
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $currentUserLevel = auth()->user()->roles->max('level') ?? 0;

        $request->validate([
            'name' => 'required|unique:roles,name',
            'level' => [
                'required', 
                'integer', 
                'min:0', 
                'max:' . $currentUserLevel 
            ]
        ], [
            // ប្រើ __() ដើម្បីហៅសារពីឯកសារ messages
            'level.max' => __('messages.error_level_max', ['level' => $currentUserLevel]),
        ]);

        Role::create([
            'name' => $request->name,
            'level' => $request->level
        ]);
        
        return response()->json(['message' => __('messages.success_create')]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $currentUserLevel = auth()->user()->roles->max('level') ?? 0;
        
        if ($role->level > $currentUserLevel) {
            return response()->json(['message' => __('messages.error_unauthorized_edit')], 403);
        }

        $request->validate([
            'name' => ['required', Rule::unique('roles', 'name')->ignore($id)],
            'level' => ['required', 'integer', 'min:0', 'max:' . $currentUserLevel]
        ]);

        $role->update([
            'name' => $request->name,
            'level' => $request->level
        ]);

        return response()->json(['message' => __('messages.success_update')]);
    }

    public function destroy($id)
    {
        $role = Role::withCount('users')->findOrFail($id);
        $currentUserLevel = auth()->user()->roles->max('level') ?? 0;

        if ($role->level > $currentUserLevel) {
            return response()->json(['message' => __('messages.error_unauthorized_delete')], 403);
        }

        if ($role->users_count > 0) {
            return response()->json([
                'message' => __('messages.error_has_users', ['name' => $role->name, 'count' => $role->users_count])
            ], 422);
        }

        $role->delete();
        return response()->json(['message' => __('messages.success_delete')]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        
        $rolesWithUsers = Role::whereIn('id', $ids)->has('users')->pluck('name')->toArray();

        if (!empty($rolesWithUsers)) {
            $names = implode(', ', $rolesWithUsers);
            return response()->json([
                'message' => __('messages.error_bulk_has_users', ['names' => $names])
            ], 422);
        }

        Role::whereIn('id', $ids)->delete();

        return response()->json(['message' => __('messages.success_bulk_delete')]);
    }

    public function getRolePermissions($id)
    {
        $role = Role::findOrFail($id);
        // សន្មតថាអ្នកមាន logic សម្រាប់ available_permissions នៅទីនេះ ឬក៏ return ទាំងអស់
        $availablePermissions = Permission::all(); 
        $rolePermissions = $role->permissions->pluck('name');

        return response()->json([
            'available_permissions' => $availablePermissions,
            'checked_permissions' => $rolePermissions
        ]);
    }

    public function updateRolePermissions(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'array'
        ]);

        $role = Role::findOrFail($request->role_id);
        $role->syncPermissions($request->permissions);

        return response()->json(['message' => __('messages.success_permission_assign')]);
    }
}