<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // ១. បង្ហាញ Form Login និង បង្កើត Captcha Code
    public function showLogin()
    {
        $captchaCode = strtoupper(Str::random(5));
        session(['captcha_code' => $captchaCode]);
        return view('admin.login', compact('captchaCode'));
    }

    // ២. Logic សម្រាប់ Login
    public function login(Request $request)
    {
        // ក. ពិនិត្យ Captcha
        if ($request->captcha !== session('captcha_code')) {
            return response()->json([
                'status' => 'error',
                'errors' => ['captcha' => ['Captcha ខុស! សូមព្យាយាមម្តងទៀត។']]
            ], 422);
        }

        // ខ. ស្វែងរក User
        $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        $user = User::where($fieldType, $request->username)->first();

        // គ. ករណីរក User មិនឃើញ
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'errors' => ['username' => ['រកមិនឃើញឈ្មោះគណនីនេះទេ (Wrong Username)']]
            ], 422);
        }

        // ឃ. ករណី Password ខុស
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'errors' => ['password' => ['លេខសម្ងាត់មិនត្រឹមត្រូវ (Wrong Password)']]
            ], 422);
        }

        // ង. បើត្រូវទាំងអស់ -> Login ចូល
        Auth::login($user);
        session()->forget('captcha_code');

        // កត់ត្រាសកម្មភាពចូលប្រព័ន្ធ
        if(function_exists('activity')) {
            activity()
                ->causedBy($user)
                ->withProperties([
                    'ip' => $request->ip(),
                    'browser' => $request->userAgent()
                ])
                ->log('logged in');
        }

        // ============================================================
        // 🔥 [ចំណុចកែប្រែ]៖ កំណត់ Route តាម Role របស់អ្នកប្រើប្រាស់
        // ============================================================
        
        $redirectUrl = route('admin.dashboard'); // Default សម្រាប់ Admin, Super Admin និង Role ផ្សេងៗ

        // ១. សម្រាប់អ្នកគិតលុយ (Cashier)
        if ($user->hasRole('Cashier')) {
            $redirectUrl = url('/pos/tables'); 
        } 
        // ២. សម្រាប់ចុងភៅ និង អ្នកធ្វើភេជ្ជៈ (Chef, Bartender)
        elseif ($user->hasRole(['Chef', 'Bartender'])) {
            $redirectUrl = url('/pos/kitchen');
        } 

        elseif ($user->hasRole('Service')) {
            $redirectUrl = url('/pos/tables');
        } 
        
        // ត្រឡប់ Link ដែលបានកំណត់ខាងលើទៅឱ្យ Javascript
        return response()->json([
            'status' => 'success',
            'redirect_url' => $redirectUrl
        ]);
    }

    // ៣. Logic សម្រាប់ Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}