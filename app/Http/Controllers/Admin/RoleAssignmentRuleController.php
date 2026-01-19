<?php

// app/Http/Controllers/Admin/RoleAssignmentRuleController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role; 
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleAssignmentRuleController extends Controller
{
    // 1. បង្ហាញតារាង Role (admin.rules.index)
    public function index()
    {
        $query = Role::withCount('assignablePermissions');
        
        // ១. យក Level ខ្លួនឯង
        $myLevel = Auth::user()->roles->max('level') ?? 0;

        // ២. Logic បង្ហាញ Role
        if ($myLevel >= 99) {
            // បើជា Super Admin: ឃើញទាំងអស់ (ដកតែខ្លួនឯងចេញ)
            $query->where('name', '!=', 'Super Admin');
        } else {
            // បើជា Admin ធម្មតា: ឃើញតែ Role ណាដែល *តូចជាង* ខ្លួនឯងដាច់ខាត (<)
            // ហាមឃើញអ្នកធំជាង ឬ អ្នកស្មើ
            $query->where('level', '<', $myLevel);
        }

        $roles = $query->get();

        return view('admin.rules.index', compact('roles'));
    }

    // 2. បង្ហាញ Form កំណត់សិទ្ធិ (admin.rules.edit)
    public function edit($id)
    {
        $targetRole = Role::with('assignablePermissions')->findOrFail($id);
        $user = Auth::user();
        $myLevel = $user->roles->max('level') ?? 0;

        // ១. Security Check: ហាមកែ Role អ្នកធំជាង ឬស្មើ (បើមិនមែន Super Admin)
        if ($myLevel < 99 && $targetRole->level >= $myLevel) {
            return abort(403, __('messages.error_unauthorized_high_level'));
        }

        // ២. Logic ទាញយក Permission មកបង្ហាញក្នុង Checkbox
        if ($myLevel >= 99) {
            // បើជា Super Admin: អាចយក Permission ទាំងអស់ក្នុង System ទៅឱ្យគេបាន
            $availablePermissions = Permission::all();
        } else {
            // [ចំណុចដែលអ្នកចង់បាន]: 
            // Admin អាចផ្ដល់សិទ្ធិឱ្យគេ បានតែសិទ្ធិណាដែល *ខ្លួនឯងមាន* ប៉ុណ្ណោះ
            $availablePermissions = $user->getAllPermissions();
        }
        
        // Group permission តាមឈ្មោះខាងមុខ
        $permissions = $availablePermissions->groupBy(function($data) {
            return explode('-', $data->name)[0]; 
        });

        return view('admin.rules.edit', [
            'role' => $targetRole,       // បញ្ជូន $targetRole ទៅជា $role
            'permissions' => $permissions
        ]);
    }

    // 3. Save ទិន្នន័យ (admin.rules.update)
    public function update(Request $request, $id)
    {
        $targetRole = Role::findOrFail($id);
        $user = Auth::user();
        $myLevel = $user->roles->max('level') ?? 0;

        // ១. Security Check: ដូច Edit ដែរ
        if ($myLevel < 99 && $targetRole->level >= $myLevel) {
            return abort(403, __('messages.error_unauthorized_high_level'));
        }
        
        // ២. Security Check (Advanced): 
        // ការពារករណី Hacker Inspect Element បន្ថែម Permission ID ដែល Admin ខ្លួនឯងអត់មាន
        if ($myLevel < 99) {
            $myPermissionIds = $user->getAllPermissions()->pluck('id')->toArray();
            $submittedIds = $request->permissions ?? [];
            
            // បើមាន ID ណាដែលផ្ញើមក តែមិនមែនជារបស់ខ្លួនឯង => Error
            if (!empty(array_diff($submittedIds, $myPermissionIds))) {
                 return abort(403, __('messages.error_security_alert_grant'));
            }
        }

        // Sync ចូលក្នុង Table 'role_assignable_permissions'
        $targetRole->assignablePermissions()->sync($request->permissions ?? []);

        return redirect()->route('admin.rules.index')
                         ->with('success', __('messages.success_rules_updated', ['name' => $targetRole->name]));
    }
}