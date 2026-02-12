<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                
                $user = Auth::user();

                // ១. សម្រាប់ Cashier (ទៅ POS)
                if ($user->hasRole('Cashier')) {
                    return redirect()->route('pos.tables');
                }

                // ២. សម្រាប់ Chef/Bartender (ទៅ Kitchen)
                if ($user->hasRole(['Chef', 'Bartender'])) {
                    return redirect()->route('pos.kitchen.view');
                }

                // ៣. សម្រាប់ Admin (ទៅ Dashboard)
                // សំខាន់៖ ត្រូវប្រាកដថា User នេះមាន Permission 'view_dashboard' ពិតមែន
                if ($user->can('view_dashboard')) {
                    return redirect()->route('admin.dashboard');
                }

                // ៤. ចុងក្រោយ៖ បើមិនចូលលក្ខខណ្ឌខាងលើសោះ អោយទៅ POS 
                if ($user->hasRole('Service')) {
                    return redirect()->route('pos.tables');
                }

                // ៥. ចុងក្រោយ៖ បើមិនចូលលក្ខខណ្ឌខាងលើសោះ អោយទៅ POS 
                // (កុំអោយទៅ Dashboard ព្រោះខ្លាច 403)
                return redirect()->route('pos.tables');
            }
        }

        return $next($request);
    }
}