<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminRedirect
{
    public function handle($request, Closure $next)
    {
        // Agar URL /admin hai
        if ($request->is('admin')) {
            if (Auth::check()) {
                return redirect()->route('dashboard');
            } else {
                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
