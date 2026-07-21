<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProviderEcoBalanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $provider = \Auth::user();

        if ($provider) {
            // Vérifier l'expiration du bonus ECO
            if ($provider->eco_bonus_expires_at && \Carbon\Carbon::now()->isAfter($provider->eco_bonus_expires_at)) {
                // Optionnel : Réinitialiser le solde si c'est un bonus expiré
                if ($provider->eco_wallet_balance > 0) {
                    $provider->eco_wallet_balance = 0;
                    $provider->eco_bonus_expires_at = null;
                    $provider->save();
                }
            }

            // Solde minimum réduit à 1 ECO (1 ECO = 1000 CFA) pour tous les chauffeurs (y compris plan FREE)
            $minBalance = 1.0;

            if ($provider->eco_wallet_balance < $minBalance) {
                return response()->json([
                    'error' => 'INSOLVENT',
                    'message' => 'Votre solde ECO est insuffisant (Minimum requis : 1 ECO = 1000 CFA). Veuillez recharger votre compte pour pouvoir passer en ligne.',
                    'min_required' => (float) $minBalance,
                    'current_balance' => (float) $provider->eco_wallet_balance
                ], 403);
            }
        }

        return $next($request);
    }
}
