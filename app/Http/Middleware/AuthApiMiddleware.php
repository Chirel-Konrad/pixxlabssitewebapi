<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthApiMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::guard('api')->check()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Non autorisé. Token manquant ou invalide.',
                ],
                401,
                ['Content-Type' => 'application/json; charset=UTF-8'],
                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
            );
        }

        return $next($request);
    }
}

