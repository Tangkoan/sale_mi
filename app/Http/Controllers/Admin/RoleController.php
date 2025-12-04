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
        $query = Role::with('permissions')->withCount('users'); 

        // ១. យក Level ខ្លួនឯង
        $myLevel = auth()->user()->roles->max('level') ?? 0;

        // ២. បើមិនមែន Super Admin (99) ទេ
        // ឃើញតែ Role ណាដែល *តូចជាង* ខ្លួនឯង (Strictly Less Than)
        if ($myLevel < 99) { 
            $query->where('level', '<', $myLevel); 
        }
        // *សម្គាល់៖ បើគាត់ជា Super Admin (99) គាត់នឹងឃើញទាំងអស់ ព្រោះ Level ទាំងអស់តូចជាង 99

        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        $perPage = $request->input('per_page', 10);
        $perPage = ($perPage == 'all') ? $query->count() : $perPage;

        $roles = $query->latest()->paginate($perPage);
        return response()->json($roles);
    }

    // នៅក្នុង RoleController.php
    public function store(Request $request)
    {
        // ១. រកមើល Level របស់អ្នកដែលកំពុង Login
        // (សន្មតថា User ម្នាក់មាន Role តែមួយ បើច្រើនយកអាធំបំផុត)
        $currentUserLevel = auth()->user()->roles->max('level') ?? 0;

        // ២. Validate
        $request->validate([
            'name' => 'required|unique:roles,name',
            'level' => [
                'required', 
                'integer', 
                'min:0', 
                // ហាមបង្កើត Role ដែលមាន Level ធំជាងខ្លួនឯង
                'max:' . $currentUserLevel 
            ]
        ], [
            'level.max' => 'You cannot create a role with a level higher than your own (' . $currentUserLevel . ').',
        ]);

        // ៣. បង្កើត Role
        // (ត្រូវប្រាកដថាអ្នកបានដាក់ 'level' ក្នុង $fillable នៃ Role Model)
        Role::create([
            'name' => $request->name,
            'level' => $request->level
        ]);
        
        return response()->json(['message' => 'Role created successfully!']);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $currentUserLevel = auth()->user()->roles->max('level') ?? 0;
        

        // ១. Security Check: ហាមកែ Role អ្នកធំ
        if ($role->level > $currentUserLevel) {
            return response()->json(['message' => 'Unauthorized: You cannot edit a role with a higher level than yours.'], 403);
        }

        $request->validate([
            'name' => ['required', Rule::unique('roles', 'name')->ignore($id)],
            'level' => ['required', 'integer', 'min:0', 'max:' . $currentUserLevel]
        ]);

        $role->update([
            'name' => $request->name,
            'level' => $request->level
        ]);

        return response()->json(['message' => 'Role updated successfully!']);
    }

    public function destroy($id)
    {
        $role = Role::withCount('users')->findOrFail($id);
        $currentUserLevel = auth()->user()->roles->max('level') ?? 0;

        // ១. Security Check: ហាមលុប Role អ្នកធំ
        if ($role->level > $currentUserLevel) {
            return response()->json(['message' => 'Unauthorized: You cannot delete a role with a higher level than yours.'], 403);
        }

        if ($role->users_count > 0) {
            return response()->json([
                'message' => "Cannot delete role '{$role->name}' because it has {$role->users_count} users assigned."
            ], 422);
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