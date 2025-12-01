<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Spatie\Permission\Models\Role; // ១. ហៅ Model Role មកប្រើ

class UserController extends Controller
{
    // ... (កូដ Profile & Password ចាស់របស់អ្នករក្សាទុកដដែល) ...

    // ==========================================
    // USER MANAGEMENT (CRUD + AJAX)
    // ==========================================

    // 1. បង្ហាញទំព័រ User List (និងបញ្ជូនឈ្មោះ Role ទៅអោយ Dropdown)
    public function userList()
    {
        $roles = Role::all(); // ទាញយក Role ទាំងអស់
        return view('admin.user.user_list', compact('roles'));
    }

    // 2. ទទួលសំណើ AJAX ដើម្បីទាញយកទិន្នន័យ User (Real-time Search)
    public function fetchUsers(Request $request)
    {
        $query = User::with('roles'); // Eager load roles

        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('email', 'like', '%' . $request->keyword . '%');
        }

        $users = $query->latest()->paginate(10);

        return response()->json($users);
    }

    // 3. បង្កើត User ថ្មី (Create)
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role'     => 'required' // តម្រូវអោយរើស Role
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // កំណត់ Role អោយ User
        $user->assignRole($request->role);

        return response()->json(['message' => 'User created successfully!']);
    }

    // 4. កែប្រែ User (Update)
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role'  => 'required'
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        // បើគេវាយ Password ថ្មី ចាំ Update, បើអត់ទេ ទុកចាស់
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Update Role (Sync មានន័យថា លុបចាស់ចេញ ដាក់ថ្មីចូល)
        $user->syncRoles([$request->role]);

        return response()->json(['message' => 'User updated successfully!']);
    }

    // 5. លុប User (Delete)
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // (Optional) លុបរូប Avatar ផងបើមាន
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully!']);
    }
}