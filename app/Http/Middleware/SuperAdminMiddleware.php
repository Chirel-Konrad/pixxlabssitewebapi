<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Traits\ApiResponse;

class SuperAdminMiddleware
{
    use ApiResponse;

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('api')->user(); // Récupère l'utilisateur authentifié

        if (!$user || $user->role !== 'superadmin') {
            return $this->errorResponse('Accès refusé. Vous devez être super administrateur.', 403);
        }

        return $next($request);
    }
}
