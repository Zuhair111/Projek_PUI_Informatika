<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdministrator
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('dashboard.login.form');
        }

        $user = Auth::user();

        if (!$user || $user->role !== 'administrator') {
            abort(403, 'Anda tidak memiliki akses ke dashboard administrator.');
        }

        return $next($request);
    }
}
