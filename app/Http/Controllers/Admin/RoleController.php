<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;

class RoleController extends Controller
{
    // 1. បង្ហាញទំព័រ Role List (ផ្ញើ Permissions ទៅអោយ Modal)
    public function index()
    {
        $permissions = Permission::all();
        return view('admin.role.role_list', compact('permissions'));
    }

    // 2. AJAX Fetch Data
    public function fetchRoles(Request $request)
    {
        $query = Role::with('permissions'); // ទាញយក Role ព្រមទាំង Permission របស់វា

        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // មិនអោយកែ ឬលុប Super Admin (ការពារ)
        // $query->where('name', '!=', 'Super Admin'); 

        $roles = $query->latest()->paginate(10);

        return response()->json($roles);
    }

    // 3. Create Role
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array'
        ]);

        $role = Role::create(['name' => $request->name]);
        
        // បញ្ចូល Permissions ទៅក្នុង Role
        $role->syncPermissions($request->permissions);

        return response()->json(['message' => 'Role created successfully!']);
    }

    // 4. Update Role
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,'.$id,
            'permissions' => 'required|array'
        ]);

        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);
        
        // Update Permissions
        $role->syncPermissions($request->permissions);

        return response()->json(['message' => 'Role updated successfully!']);
    }

    // 5. Delete Role
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully!']);
    }
}