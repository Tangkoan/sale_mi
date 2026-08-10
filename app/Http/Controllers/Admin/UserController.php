<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // ==========================================
    // HELPER: កំណត់សិទ្ធិមើល Role (Security)
    // ==========================================
    private function getAllowedRoles()
    {
        $currentUser = Auth::user();

        // ១. បើគាត់ជា Super Admin (ធំបំផុត)
        if ($currentUser->hasRole('Super Admin')) {
            return Role::all();
        }

        // ២. Admin ធម្មតាឃើញតែ Role ដែលតូចជាងខ្លួន
        $currentLevel = $currentUser->roles->max('level') ?? 0;
        return Role::where('level', '<', $currentLevel)->get();
    }

    // ==========================================
    // 1. VIEW & LIST (GET)
    // ==========================================
    public function userList()
    {
        $roles = $this->getAllowedRoles(); 
        return view('admin.user.user_list', compact('roles'));
    }

    public function fetchUsers(Request $request)
    {
        $query = User::with('roles');

        // Security: Admin ធម្មតាមិនអាចឃើញ Super Admin ទេ
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
        $users = ($perPage === 'all') ? $query->latest()->paginate(999999) : $query->latest()->paginate((int)$perPage);

        return response()->json($users);
    }

    // ==========================================
    // 2. CREATE (POST)
    // ==========================================
    public function store(Request $request)
    {
        $allowedRoles = $this->getAllowedRoles()->pluck('name')->toArray();

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role'     => ['required', Rule::in($allowedRoles)]
        ], [
            'email.unique' => __('messages.email_duplicate'),
            'role.in'      => __('messages.role_permission_denied'),
            'required'     => __('messages.field_required'),
            'min'          => __('messages.password_min'),
        ]);

        if ($validator->fails()) {
            // Check ករណី Email ស្ទួន
            if ($validator->errors()->has('email')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => __('messages.email_duplicate'),
                    'errors'  => $validator->errors()
                ], 422);
            }
            return response()->json([
                'status'  => 'error',
                'message' => __('messages.invalid_data'),
                'errors'  => $validator->errors()
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // បើមានបញ្ចូល PIN គឺត្រូវ Hash វា
            if ($request->filled('pin')) {
                $data['pin'] = Hash::make($request->pin);
            }

            $user->assignRole($request->role);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->withProperties(['role' => $request->role])
                ->log('create user role');

            return response()->json([
                'status'  => 'success',
                'message' => __('messages.user_created'),
            ], 200);
        });
    }

    public function profile() { $user = Auth::user(); return view('admin.infouser.profile', compact('user')); }
    
    // ==========================================
    // PROFILE UPDATE (កូដដែលត្រូវកែ)
    // ==========================================
    public function updateProfile(Request $request) 
    { 
        $user = Auth::user();

        // 1. Validation
        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:users,email,' . $user->id, // unique តែអនុញ្ញាតសម្រាប់ ID ខ្លួនឯង
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // កំណត់ប្រភេទរូបភាព
        ]);

        // 2. ការប្តូររូបភាព (Avatar)
        if ($request->hasFile('avatar')) {
            // លុបរូបចាស់ចោល បើមាន
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // ដាក់រូបថ្មីចូល
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        // 3. Update ឈ្មោះ និង អ៊ីមែល
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        // 4. Return ត្រឡប់ទៅវិញ
        return back()->with('success', __('messages.profile_updated')); 
    }
    
    public function password() { return view('admin.infouser.password'); }
    
    // ==========================================
    // PASSWORD UPDATE (កូដពេញលេញ)
    // ==========================================
    public function updatePassword(Request $request)
    {
        // 1. Validation
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed', // ត្រូវតែមាន password_confirmation field ក្នុង form
        ], [
            'current_password.required' => __('messages.current_password_required'),
            'password.required' => __('messages.new_password_required'),
            'password.min' => __('messages.password_min'),
            'password.confirmed' => __('messages.password_confirmed_error'),
        ]);

        $user = Auth::user();

        // 2. ពិនិត្យមើលថាពាក្យសម្ងាត់ចាស់ត្រឹមត្រូវឬអត់?
        if (!Hash::check($request->current_password, $user->password)) {
            // បើខុស Return ទៅវិញជាមួយ Error
            return back()->withErrors(['current_password' => __('messages.current_password_incorrect')]);
        }

        // 3. Update ពាក្យសម្ងាត់ថ្មី
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // 4. Return Success
        return back()->with('success', __('messages.password_changed_success'));
    }
    
    // ==========================================
    // 3. UPDATE (PUT/PATCH)
    // ==========================================
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = Auth::user();

        // Security: ហាមកែ Super Admin បើខ្លួនឯងមិនមែន
        if ($user->hasRole('Super Admin') && !$currentUser->hasRole('Super Admin')) {
            return response()->json(['status' => 'error', 'message' => __('messages.unauthorized_action')], 403);
        }

        $allowedRoles = $this->getAllowedRoles()->pluck('name')->toArray();

        // Validator
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            // unique:table,column,except_id
            'email' => 'required|email|unique:users,email,' . $id, 
            'role'  => ['required', Rule::in($allowedRoles)]
        ], [
            'email.unique' => __('messages.email_duplicate'),
            'role.in'      => __('messages.role_permission_denied'),
            'required'     => __('messages.field_required'),
        ]);

        if ($validator->fails()) {
            if ($validator->errors()->has('email')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => __('messages.email_duplicate'),
                    'errors'  => $validator->errors()
                ], 422);
            }
            return response()->json([
                'status'  => 'error',
                'message' => __('messages.invalid_data'),
                'errors'  => $validator->errors()
            ], 422);
        }

        // Save Data
        return DB::transaction(function () use ($request, $user) {
            $data = [
                'name'  => $request->name,
                'email' => $request->email,
            ];

            if ($request->password) {
                $data['password'] = Hash::make($request->password);
            }

            // បើមានបញ្ចូល PIN គឺត្រូវ Hash វាដើម្បី Update
            if ($request->filled('pin')) {
                $data['pin'] = Hash::make($request->pin);
            }

            $user->update($data);
            $user->syncRoles([$request->role]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->withProperties(['role' => $request->role])
                ->log('updated user role');

            return response()->json([
                'status'  => 'success',
                'message' => __('messages.user_updated'),
            ]);
        });
    }

    // ==========================================
    // 4. DELETE (DELETE)
    // ==========================================
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->hasRole('Super Admin') && !Auth::user()->hasRole('Super Admin')) {
            return response()->json(['status' => 'error', 'message' => __('messages.unauthorized_action')], 403);
        }

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return response()->json([
            'status'  => 'success',
            'message' => __('messages.user_deleted')
        ]);
    }

    // ==========================================
    // 5. BULK DELETE
    // ==========================================
    public function bulkDestroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => __('messages.invalid_data')], 422);
        }

        $users = User::whereIn('id', $request->ids)->get();
        $deletedCount = 0;
        $currentUser = Auth::user();

        foreach ($users as $user) {
            if ($user->hasRole('Super Admin') && !$currentUser->hasRole('Super Admin')) continue;

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->delete();
            $deletedCount++;
        }

        return response()->json([
            'status'  => 'success',
            'message' => __('messages.bulk_delete_success', ['count' => $deletedCount])
        ]);
    }

    // ==========================================
    // 6. BULK UPDATE
    // ==========================================
    public function bulkUpdate(Request $request)
    {
        $allowedRoles = $this->getAllowedRoles()->pluck('name')->toArray();

        $validator = Validator::make($request->all(), [
            'ids'  => 'required|array',
            'role' => ['required', 'exists:roles,name', Rule::in($allowedRoles)]
        ], [
            'role.in' => __('messages.role_permission_denied')
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => __('messages.invalid_data'), 'errors' => $validator->errors()], 422);
        }

        $users = User::whereIn('id', $request->ids)->get();
        $updatedCount = 0;
        $currentUser = Auth::user();

        foreach($users as $user) {
            if ($user->hasRole('Super Admin') && !$currentUser->hasRole('Super Admin')) continue;

            $user->syncRoles([$request->role]);
            $updatedCount++;
        }

        return response()->json([
            'status'  => 'success',
            'message' => __('messages.bulk_update_success', ['count' => $updatedCount])
        ]);
    }
}