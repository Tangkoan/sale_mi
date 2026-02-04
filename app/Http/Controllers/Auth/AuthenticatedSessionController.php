<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // 🔥 កូដបន្ថែមសម្រាប់ការ Check Role
        $user = Auth::user();

        if ($user->hasRole(['Admin', 'Super Admin'])) {
            return redirect()->intended('/dashboard');
        }

        if ($user->hasRole('Cashier')) { // ដាក់ឈ្មោះ Role អោយត្រូវនឹងក្នុង DB
            return redirect()->intended('/pos/tables');
        }

        if ($user->hasRole(['Chef', 'Bartender'])) { // Role ចុងភៅ និង ភេសជ្ជៈ
            return redirect()->intended('/pos/kitchen');
        }

        // Role ផ្សេងៗអោយទៅ Dashboard ដែរ
        return redirect()->intended('/dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
