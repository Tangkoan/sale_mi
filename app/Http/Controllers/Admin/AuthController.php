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
    // ==========================================
    // ក. ករណី Login ដោយប្រើ PIN សុទ្ធ (បញ្ចូលតែ PIN)
    // ==========================================
    if ($request->login_method === 'pin') {
        
        $authenticatedUser = null;
        // ទាញយកតែ User ណាដែលមានកំណត់ PIN ប៉ុណ្ណោះ
        $users = User::whereNotNull('pin')->get(); 

        foreach ($users as $u) {
            if (Hash::check($request->pin, $u->pin)) {
                $authenticatedUser = $u;
                break;
            }
        }

        if (!$authenticatedUser) {
            return response()->json([
                'status' => 'error',
                'errors' => ['pin' => ['លេខកូដ PIN មិនត្រឹមត្រូវទេ']]
            ], 422);
        }

        Auth::login($authenticatedUser);
        $user = $authenticatedUser;
    } 
    // ==========================================
    // ខ. ករណី Login ដោយប្រើ Username/Email និង Password ធម្មតា
    // ==========================================
    else {
        // ពិនិត្យ Captcha តែពេលប្រើ Password ធម្មតាប៉ុណ្ណោះ
        if ($request->captcha !== session('captcha_code')) {
            return response()->json([
                'status' => 'error',
                'errors' => ['captcha' => ['Captcha ខុស! សូមព្យាយាមម្តងទៀត។']]
            ], 422);
        }

        $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        $user = User::where($fieldType, $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'errors' => ['username' => ['ឈ្មោះគណនី ឬ លេខសម្ងាត់មិនត្រឹមត្រូវ']]
            ], 422);
        }

        Auth::login($user);
    }

    session()->forget('captcha_code');

    // ... កូដ Redirect URL ខាងក្រោមរក្សាទុកដដែល ...
    $redirectUrl = route('admin.dashboard'); 
    if ($user->hasRole('Cashier') || $user->hasRole('Service')) {
        $redirectUrl = url('/pos/tables'); 
    } elseif ($user->hasRole(['Chef', 'Bartender'])) {
        $redirectUrl = url('/pos/kitchen');
    } 
    
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