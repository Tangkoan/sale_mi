<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule; // [សំខាន់] ត្រូវហៅអា នេះមកប្រើ

class UserController extends Controller
{
    // ==========================================
    // [បន្ថែមថ្មី] HELPER FUNCTION: កំណត់សិទ្ធិមើល Role
    // ==========================================
    private function getAllowedRoles()
    {
        $currentUser = Auth::user();

        // ១. បើគាត់ជា Super Admin (Level ខ្ពស់បំផុត ឬមានឈ្មោះពិសេស)
        if ($currentUser->hasRole('Super Admin')) {
            return Role::all();
        }

        // ២. ទាញយក Level របស់ User បច្ចុប្បន្ន
        // (សន្មតថា User ម្នាក់មាន Role តែមួយ, បើមានច្រើនយកអាធំបំផុត)
        $currentLevel = $currentUser->roles->max('level') ?? 0;

        // ៣. Query យកតែ Role ណាដែលតូចជាង Level របស់គាត់
        return Role::where('level', '<', $currentLevel)->get();
    }

    // 1. បង្ហាញទំព័រ User List
    public function userList()
    {
        // [កែប្រែ] លែងប្រើ Role::all() ហើយ ប្រើ Function ខាងលើជំនួស
        // ដើម្បីកុំឱ្យ Admin ធម្មតាឃើញ Role "Super Admin" ក្នុង Dropdown
        $roles = $this->getAllowedRoles(); 
        
        return view('admin.user.user_list', compact('roles'));
    }

    // ... (Profile functions remain the same) ...
    public function profile() { $user = Auth::user(); return view('admin.infouser.profile', compact('user')); }
    public function updateProfile(Request $request) { /* ... keep your existing code ... */ return back()->with('success', 'Profile updated'); }
    public function password() { return view('admin.infouser.password'); }
    public function updatePassword(Request $request) { /* ... keep your existing code ... */ return back()->with('success', 'Password changed'); }

    // ==========================================
    // USER MANAGEMENT (CRUD + AJAX)
    // ==========================================

    // 2. ទទួលសំណើ AJAX ដើម្បីទាញយកទិន្នន័យ User
    public function fetchUsers(Request $request)
    {
        $query = User::with('roles');

        // [បន្ថែមថ្មី] Security: បើមិនមែន Super Admin ទេ ហាមមើលឃើញ User ដែលជា Super Admin
        // នេះការពារកុំឱ្យ Admin ធម្មតាដឹងថាគណនីណាខ្លះជាមេធំ ឬលួចកែ
        if (!Auth::user()->hasRole('Super Admin')) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Super Admin');
            });
        }

        if ($request->keyword) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('email', 'like', '%' . $request->keyword . '%');
            });
        }

        $perPage = $request->input('per_page', 10);
        
        if ($perPage === 'all') {
            $users = $query->latest()->paginate(999999); 
        } else {
            $users = $query->latest()->paginate((int)$perPage);
        }

        return response()->json($users);
    }

    // 3. បង្កើត User ថ្មី (Create)
    public function store(Request $request)
    {
        // [បន្ថែមថ្មី] ទាញយកឈ្មោះ Role ដែលអនុញ្ញាតអោយប្រើ
        $allowedRoles = $this->getAllowedRoles()->pluck('name')->toArray();

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role'     => [
                'required', 
                // [បន្ថែមថ្មី - សំខាន់បំផុត!] Security Rule!
                // ត្រួតពិនិត្យថា Role ដែលផ្ញើមករមាននៅក្នុង List ដែលអនុញ្ញាតឬអត់
                // បើ Admin ព្យាយាម Hack ដាក់ 'Super Admin' វានឹង Error ភ្លាម
                Rule::in($allowedRoles) 
            ]
        ], [
            'role.in' => 'You do not have permission to assign this role.' // Message បើសិនគេលួច Hack
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return response()->json(['message' => 'User created successfully!']);
    }

    // 4. កែប្រែ User (Update)
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = Auth::user();

        // [បន្ថែមថ្មី] Security Check: 
        // បើ User ដែលគេចង់កែជា Super Admin ហើយអ្នកកែមិនមែន Super Admin => ហាម!
        if ($user->hasRole('Super Admin') && !$currentUser->hasRole('Super Admin')) {
            return response()->json(['message' => 'Unauthorized! You cannot update a Super Admin account.'], 403);
        }

        // [បន្ថែមថ្មី] យក Role ដែលអនុញ្ញាត
        $allowedRoles = $this->getAllowedRoles()->pluck('name')->toArray();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role'  => [
                'required', 
                // [បន្ថែមថ្មី] ការពារដូច store ដែរ
                Rule::in($allowedRoles)
            ]
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Update Role
        $user->syncRoles([$request->role]);

        return response()->json(['message' => 'User updated successfully!']);
    }

    // 5. លុប User (Delete)
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // [បន្ថែមថ្មី] Security Check: ហាម Admin ធម្មតាលុប Super Admin
        if ($user->hasRole('Super Admin') && !Auth::user()->hasRole('Super Admin')) {
            return response()->json(['message' => 'Unauthorized! You cannot delete a Super Admin account.'], 403);
        }

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully!']);
    }

    // 1. Bulk Delete
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        $users = User::whereIn('id', $request->ids)->get();
        $deletedCount = 0;
        $currentUser = Auth::user();

        foreach ($users as $user) {
            // [បន្ថែមថ្មី] Security Check ក្នុង Loop
            // រំលង (Skip) ការលុប ប្រសិនបើជា Super Admin ហើយអ្នកលុបមិនមែន
            if ($user->hasRole('Super Admin') && !$currentUser->hasRole('Super Admin')) {
                continue; 
            }

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->delete();
            $deletedCount++;
        }

        return response()->json(['message' => $deletedCount . ' users deleted successfully!']);
    }

    // 2. Bulk Edit
    public function bulkUpdate(Request $request)
    {
        // [បន្ថែមថ្មី] យក Role ដែលអនុញ្ញាត
        $allowedRoles = $this->getAllowedRoles()->pluck('name')->toArray();

        $request->validate([
            'ids' => 'required|array',
            'role' => [
                'required', 
                'exists:roles,name',
                // [បន្ថែមថ្មី] ការពារមិនឱ្យ Bulk Update ទៅជា Super Admin
                Rule::in($allowedRoles)
            ]
        ]);

        $users = User::whereIn('id', $request->ids)->get();
        $updatedCount = 0;
        $currentUser = Auth::user();

        foreach($users as $user) {
            // [បន្ថែមថ្មី] Security Check: 
            // ហាមប្តូរ Role របស់ Super Admin បើអ្នកធ្វើមិនមែន Super Admin
            if ($user->hasRole('Super Admin') && !$currentUser->hasRole('Super Admin')) {
                continue;
            }

            $user->syncRoles([$request->role]);
            $updatedCount++;
        }

        return response()->json(['message' => 'Roles updated for ' . $updatedCount . ' users!']);
    }
}