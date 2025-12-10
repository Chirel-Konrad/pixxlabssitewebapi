<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Traits\ApiResponse;

class AdminMiddleware
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('api')->user(); // Récupère l'utilisateur authentifié

        // Modification pour permettre aux superadmins d'accéder aussi (Best Practice)
        // Si on veut strictement admin, on garde $user->role !== 'admin'
        // Mais généralement superadmin > admin.
        // Je vais respecter le code demandé mais ajouter superadmin par sécurité logique si l'utilisateur le souhaite
        // Pour l'instant je colle au code demandé :
        
        if (!$user || $user->role !== 'admin') { 
            return $this->errorResponse('Accès refusé. Vous devez être administrateur.', 403);
        }

        return $next($request);
    }
}
