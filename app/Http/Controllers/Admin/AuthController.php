<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\RateLimiter; // កុំភ្លេច Import RateLimiter
use App\Models\BlockedIp;

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
        // ក. ករណី Login ដោយប្រើ PIN សុទ្ធ
        // ==========================================
        if ($request->login_method === 'pin') {
            
            // ប្រើ Session ID ដើម្បី Block តែ Device នេះ (មិនប៉ះពាល់ Device ផ្សេងក្នុង WiFi តែមួយ)
            $deviceSessionId = $request->session()->getId();
            $throttleKey = 'pin_login_' . $deviceSessionId;
            
            $maxAttempts = 5; // អនុញ្ញាតឲ្យវាយខុស ៥ដង
            $decaySeconds = 3600; // Block រយៈពេល ១ម៉ោង

            // ១. ពិនិត្យមើលថាតើ Device នេះកំពុងជាប់ Block ឬទេ?
            if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
                $seconds = RateLimiter::availableIn($throttleKey);
                return response()->json([
                    'status' => 'error',
                    'errors' => ['pin' => ["ឧបករណ៍នេះត្រូវបាន Block! សូមរង់ចាំ " . ceil($seconds/60) . " នាទី។"]]
                ], 429);
            }

            // ២. ស្វែងរក User ដោយផ្ទៀងផ្ទាត់ Hash របស់ PIN
            $authenticatedUser = null;
            $users = User::whereNotNull('pin')->get(); 
            
            foreach ($users as $u) {
                if (Hash::check($request->pin, $u->pin)) {
                    $authenticatedUser = $u;
                    break;
                }
            }

            // ៣. ករណីវាយខុស
            if (!$authenticatedUser) {
                RateLimiter::hit($throttleKey, $decaySeconds); 
                $retriesLeft = RateLimiter::retriesLeft($throttleKey, $maxAttempts);

                // បើវាយខុសគ្រប់ ៥ ដង -> Save ចូល DB សម្រាប់ឲ្យ Admin មើលក្នុង Management UI
                if ($retriesLeft === 0) {
                    // បង្ហាញ IP និងកន្ទុយ Session ខ្លីៗដើម្បីឲ្យ Admin ងាយចំណាំ
                    $identifier = $request->ip() . ' (Device: ' . substr($deviceSessionId, 0, 5) . ')';
                    
                    BlockedIp::updateOrCreate(
                        ['ip_address' => $identifier],
                        ['expires_at' => now()->addSeconds($decaySeconds)]
                    );
                }

                return response()->json([
                    'status' => 'error',
                    'errors' => ['pin' => ["លេខកូដមិនត្រឹមត្រូវ! អ្នកអាចសាកល្បងបាន $retriesLeft ដងទៀត។"]]
                ], 422);
            }

            // ៤. ករណីវាយត្រូវ -> Clear ការរាប់ការវាយខុសទាំងក្នុង Cache និង DB
            RateLimiter::clear($throttleKey);
            $identifier = $request->ip() . ' (Device: ' . substr($deviceSessionId, 0, 5) . ')';
            BlockedIp::where('ip_address', $identifier)->delete();

            Auth::login($authenticatedUser);
            $user = $authenticatedUser;
        } 
        // ==========================================
        // ខ. ករណី Login ដោយប្រើ Username/Email និង Password ធម្មតា
        // ==========================================
        else {
            // ក. ពិនិត្យ Captcha
            if ($request->captcha !== session('captcha_code')) {
                return response()->json([
                    'status' => 'error',
                    'errors' => ['captcha' => ['Captcha ខុស! សូមព្យាយាមម្តងទៀត។']]
                ], 422);
            }

            // ខ. ស្វែងរក User តាម Username ឬ Email
            $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
            $user = User::where($fieldType, $request->username)->first();

            // គ. ករណីរក User មិនឃើញ
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'errors' => ['username' => ['រកមិនឃើញឈ្មោះគណនី ឬ Email នេះទេ']]
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
        }

        // ==========================================
        // គ. កិច្ចការបន្ទាប់ពី Login ជោគជ័យ (ដំណើរការដូចគ្នាទាំង PIN និង Password)
        // ==========================================
        
        session()->forget('captcha_code');

        // កត់ត្រាសកម្មភាពចូលប្រព័ន្ធ (Activity Log)
        if(function_exists('activity')) {
            activity()
                ->causedBy($user)
                ->withProperties([
                    'ip' => $request->ip(),
                    'browser' => $request->userAgent()
                ])
                ->log('logged in');
        }

        // កំណត់ Route ទៅកាន់ Dashboard ឬទំព័រ POS ទៅតាម Role
        $redirectUrl = route('admin.dashboard'); // Default

        if ($user->hasRole('Cashier') || $user->hasRole('Service')) {
            $redirectUrl = url('/pos/tables'); 
        } 
        elseif ($user->hasRole(['Chef', 'Bartender'])) {
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