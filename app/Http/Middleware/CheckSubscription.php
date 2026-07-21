<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Models\ServiceType;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // Si la requête contient un type de service spécifique
        if ($request->has('service_type')) {
            $serviceType = ServiceType::find($request->service_type);

            if ($serviceType && $serviceType->requires_pro_subscription) {
                // hasActiveSubscription() will check marketplace subscription first, then legacy provider subscription
                if (!$user->hasActiveSubscription()) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'error' => 'Pass Premium Requis',
                            'message' => 'Ce service (' . $serviceType->name . ') nécessite un abonnement actif.',
                            'requires_subscription' => true
                        ], 403);
                    }
                    return redirect('dashboard')->with('flash_error', 'Abonnement requis pour ce service.');
                }
            }
        }

        return $next($request);
    }
}
