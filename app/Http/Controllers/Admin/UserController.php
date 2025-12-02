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

    // 1. បង្ហាញទំព័រ User List (និងបញ្ជូនឈ្មោះ Role ទៅអោយ Dropdown)
    public function userList()
    {
        $roles = Role::all(); // ទាញយក Role ទាំងអស់
        return view('admin.user.user_list', compact('roles'));
    }


    // 1. បង្ហាញផ្ទាំង User Info
    public function profile()
    {
        $user = Auth::user();
        return view('admin.infouser.profile', compact('user'));
    }

    // 2. រក្សាទុកការកែប្រែ User Info
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // ដាក់រូបបានត្រឹម 2MB
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // ២. ដំណើរការប្តូររូប (Logic សំខាន់នៅទីនេះ)
        if ($request->hasFile('avatar')) {

            // ក. ពិនិត្យមើល៖ បើ User មានរូបចាស់ ហើយរូបនោះពិតជាមានក្នុង Server មែន
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                // ខ. លុបរូបចាស់នោះចោលភ្លាម
                Storage::disk('public')->delete($user->avatar);
            }

            // គ. ដាក់រូបថ្មីចូល
            $path = $request->file('avatar')->store('avatars', 'public');
            
            // ឃ. យកឈ្មោះរូបថ្មីទៅត្រៀម Update ចូល Database
            $data['avatar'] = $path;
        }

        // ៣. Update ទិន្នន័យ
        $user->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }

    // 3. បង្ហាញផ្ទាំង Change Password
    public function password()
    {
        return view('admin.infouser.password');
    }

    // 4. រក្សាទុកការប្ដូរ Password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed', // confirmed នឹង check ជាមួយ field password_confirmation
        ]);

        $user = Auth::user();

        // ផ្ទៀងផ្ទាត់ Password ចាស់
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password does not match!']);
        }

        // Update Password ថ្មី
        User::whereId($user->id)->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password changed successfully!');
    }

    // ==========================================
    // USER MANAGEMENT (CRUD + AJAX)
    // ==========================================

    

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