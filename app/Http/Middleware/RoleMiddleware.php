<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        if (!in_array(Auth::user()->role, $roles)) {
            return match (Auth::user()->role) {
                'admin'  => redirect()->route('admin.dashboard'),
                default  => redirect()->route('presensi'),
            };
        }

        return $next($request);
    }
}