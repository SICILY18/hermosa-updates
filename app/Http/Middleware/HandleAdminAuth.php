<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleAdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated via our session system
        if (!session('admin_authenticated') || !session('staff_data')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect('/');
        }

        // Ensure session doesn't expire and refresh session
        $request->session()->regenerate();

        return $next($request);
    }
} 