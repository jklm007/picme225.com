<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureFeatureLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $feature
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } elseif (Auth::guard('provider')->check()) {
                $user = Auth::guard('provider')->user();
            }
        }

        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated.'
            ], 401);
        }

        if (!hasFeature($user, $feature)) {
            return response()->json([
                'error' => "Votre niveau d'abonnement est insuffisant pour accéder à cette fonctionnalité ({$feature}). Veuillez passer au niveau requis.",
                'required_feature' => $feature
            ], 403);
        }

        return $next($request);
    }
}
