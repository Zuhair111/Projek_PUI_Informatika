<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTokenIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if (!$bearerToken) {
            return ApiResponse::error('Token tidak ditemukan.', [], 401);
        }

        $accessToken = PersonalAccessToken::findToken($bearerToken);

        if (!$accessToken) {
            return ApiResponse::error('Token tidak valid.', [], 401);
        }

        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return ApiResponse::error('Token sudah kedaluwarsa.', [], 401);
        }

        $authenticatedUser = $accessToken->tokenable->withAccessToken($accessToken);
        Auth::setUser($authenticatedUser);
        $request->setUserResolver(static fn () => $authenticatedUser);

        return $next($request);
    }
}
