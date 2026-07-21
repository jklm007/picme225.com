<?php

namespace App\Http\Controllers\ProviderResources;

use App\Models\SubscriptionPlan;
use App\Models\Provider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Auth;
use DB;

class SubscriptionController extends Controller
{
    /**
     * List all available subscription plans for providers.
     */
    public function index()
    {
        $provider = Auth::user();
        $provider->load(['service.service_type']);

        // Identifier le service principal du chauffeur
        $serviceId = 1; // Par défaut Taxi
        if ($provider->service && $provider->service->service_type_id) {
            $serviceId = $provider->service->service_type_id;
        } elseif ($provider->service_type_id) {
            $serviceId = $provider->service_type_id;
        }

        // ─────────────────────────────────────────────────────────────
        // Catégories en COMMISSION PURE — Pas de plan d'abonnement
        // Location (ID 3) : commission fixe sur le montant de la location
        // Voyage   (ID 4) : commission fixe par ticket vendu
        // ─────────────────────────────────────────────────────────────
        $commissionOnlyServiceIds = [3, 4];
        if (in_array($serviceId, $commissionOnlyServiceIds)) {
            // Récupérer le taux de commission depuis le service_type du chauffeur
            $commissionRate = 20; // Taux par défaut
            if ($provider->service && $provider->service->service_type) {
                $commissionRate = $provider->service->service_type->commission_percentage ?? 20;
            }

            $categoryLabel = $serviceId === 3 ? 'Location' : 'Voyage';
            $description   = $serviceId === 3
                ? "Aucun abonnement requis. Vous ne payez qu'une commission de {$commissionRate}% sur chaque réservation de location complétée."
                : "Aucun abonnement requis. Vous ne payez qu'une commission de {$commissionRate}% sur chaque ticket de voyage complété.";

            return response()->json([
                'plans'               => [],
                'current_plan'        => null,
                'level'               => 'COMMISSION',
                'is_commission_only'  => true,
                'commission_rate'     => $commissionRate,
                'category_label'      => $categoryLabel,
                'description'         => $description,
                'service_id'          => $serviceId,
                'is_communal'         => false,
            ]);
        }

        // Récupérer les plans pour le service du chauffeur (Taxi, Livraison, Communal…)
        $plans = SubscriptionPlan::where('status', 'active')
            ->where('service_id', $serviceId)
            ->orderBy('price', 'asc')
            ->get();

        // Fallback Taxi uniquement si le service n'est ni Location ni Voyage
        if ($plans->isEmpty()) {
            $plans = SubscriptionPlan::where('status', 'active')
                ->where('service_id', 1)
                ->orderBy('price', 'asc')
                ->get();
        }

        // Indiquer si le service est de type Communal pour l'app
        $communalServiceIds = [6]; // ID 6 = Partage / Communal
        $isCommunal = in_array($serviceId, $communalServiceIds);

        // Transformer les plans pour inclure le plan FREE virtuel et enrichir les descriptions
        $mappedPlans = collect();

        // 1. Ajouter le plan virtuel FREE/GRATUIT en premier
        if ($isCommunal) {
            $freeDescription = 'Plan communal par défaut. Zéro loyer d\'abonnement. Commission de 25%. Variante ride (Partagé, Arrêt PDP) : Inclus par nature. Variante ride (Privé) : NON ACCESSIBLE. Un abonnement (ECO, PRO ou GOLD) est requis pour déverrouiller le mode Privé.';
        } else {
            $freeDescription = 'Plan standard par défaut. Zéro loyer d\'abonnement. Commission standard de 25%. Variante ride (Privé) : Inclus par nature. Variante ride (Partagé, Arrêt PDP) : NON ACCESSIBLE. Un abonnement (ECO, PRO ou GOLD) est requis pour déverrouiller ces variantes.';
        }
        
        $freePlan = new SubscriptionPlan();
        $freePlan->id = 0;
        $freePlan->service_id = $serviceId;
        $freePlan->name = 'FREE / GRATUIT';
        $freePlan->description = $freeDescription;
        $freePlan->price = 0;
        $freePlan->period = 'MONTHLY';
        $freePlan->commission_type = 'percentage';
        $freePlan->commission_value = 25.00;
        $freePlan->priority = 100;
        $freePlan->priority_weight = 0;
        $freePlan->max_categories = 1;
        $freePlan->status = 'active';
        $mappedPlans->push($freePlan);

        // 2. Mapper et enrichir les plans existants
        foreach ($plans as $plan) {
            $nameUpper = strtoupper($plan->name);
            
            if (str_contains($nameUpper, 'ECO')) {
                $plan->priority = 300;
                $plan->description = 'Abonnement économique. Variante ride (Partagé, Privé, Arrêt PDP) : Accès complet déverrouillé et inclus ! Commission réduite à ' . $plan->commission_value . '%.';
            } elseif (str_contains($nameUpper, 'PRO')) {
                $plan->priority = 600;
                $plan->description = 'Abonnement Professionnel. Variante ride (Partagé, Privé, Arrêt PDP) : Accès complet déverrouillé et inclus ! Commission super réduite à ' . $plan->commission_value . '%.';
            } elseif (str_contains($nameUpper, 'GOLD')) {
                $plan->priority = 1000;
                $plan->description = 'Abonnement Élite Gold. Variante ride (Partagé, Privé, Arrêt PDP) : Accès complet déverrouillé et inclus ! Zéro commission variable (Frais fixe de ' . $plan->fixed_fee . ' CFA par course).';
            } else {
                $plan->description = $plan->description . ' Variante ride (Partagé, Privé, Arrêt PDP) : Accès complet déverrouillé et inclus !';
            }
            
            $mappedPlans->push($plan);
        }

        $currentPlan = $provider->subscriptionPlan;
        $expiresAt   = $provider->subscription_expires_at;

        if ($expiresAt && !($expiresAt instanceof \Carbon\Carbon)) {
            try {
                $expiresAt = \Carbon\Carbon::parse($expiresAt);
            } catch (\Exception $e) {
                $expiresAt = null;
            }
        }

        // $isCommunal a déjà été défini plus haut

        return response()->json([
            'plans'          => $mappedPlans,
            'current_plan'   => $currentPlan,
            'level'          => $currentPlan ? $currentPlan->name : 'FREE',
            'max_categories' => $currentPlan ? $currentPlan->max_categories : 1,
            'expires_at'     => ($expiresAt instanceof \Carbon\Carbon) ? $expiresAt->toDateTimeString() : $expiresAt,
            'service_id'     => $serviceId,
            'is_communal'    => $isCommunal,
        ]);
    }

    /**
     * Subscribe to a plan using ECO wallet balance.
     */
    public function subscribe(Request $request)
    {
        if ($request->input('plan_id') == 0) {
            $provider = Auth::user();
            try {
                DB::transaction(function () use ($provider) {
                    $provider->subscription_plan_id = null;
                    $provider->subscription_level = 'free';
                    $provider->subscription_expires_at = null;
                    $provider->save();
                });

                $freshProvider = $provider->fresh(['subscriptionPlan']);
                $freshProvider->level = 'FREE';
                $freshProvider->max_categories = 1;

                return response()->json([
                    'message' => 'Vous êtes maintenant sur le plan FREE / GRATUIT !',
                    'provider' => $freshProvider
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Une erreur est survenue lors du passage au plan gratuit.'], 500);
            }
        }

        $this->validate($request, [
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $provider = Auth::user();
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        $priceInEco = $plan->price / 1000.0;
        if ($provider->eco_wallet_balance < $priceInEco) {
            return response()->json(['error' => 'Solde ECO insuffisant pour cet abonnement. Il vous faut ' . $priceInEco . ' ECO (' . $plan->price . ' CFA).'], 400);
        }

        try {
            DB::transaction(function () use ($provider, $plan, $priceInEco) {
                // Débit du portefeuille
                $provider->eco_wallet_balance -= $priceInEco;

                // Mise à jour de l'abonnement
                $provider->subscription_plan_id = $plan->id;

                // Dériver le niveau depuis le nom du plan (ex: COMMUNAL GOLD → gold)
                $planNameUpper = strtoupper($plan->name);
                if (str_contains($planNameUpper, 'GOLD')) {
                    $level = 'gold';
                } elseif (str_contains($planNameUpper, 'PRO')) {
                    $level = 'pro';
                } elseif (str_contains($planNameUpper, 'ECO')) {
                    $level = 'eco';
                } else {
                    $level = 'free';
                }
                $provider->subscription_level = $level;

                // Expiration selon la période du plan
                $days = ($plan->period === 'WEEKLY') ? 7 : 30;
                $provider->subscription_expires_at = Carbon::now()->addDays($days);

                // Recharge cumulative du "Portefeuille de Priorité"
                if ($level === 'gold') {
                    $provider->priority += 1000;
                } elseif ($level === 'pro') {
                    $provider->priority += 600;
                } elseif ($level === 'eco') {
                    $provider->priority += 300;
                } else {
                    $provider->priority += 100;
                }

                $provider->save();

                // Synchronisation Blockchain
                try {
                    $web3Service = new \App\Services\Web3Service();
                    $providerWallet = $provider->wallet_address ?? '0x' . strtoupper(substr(md5($provider->id), 0, 40));

                    $web3Service->recordSubscriptionPayment(
                        $providerWallet,
                        $plan->id,
                        $plan->price,
                        $plan->name
                    );
                } catch (\Exception $e) {
                    \Log::error('Blockchain sync failed for subscription: ' . $e->getMessage());
                    // On continue même si la blockchain échoue (mode dégradé)
                }
            });

            $freshProvider = $provider->fresh(['subscriptionPlan']);
            $freshProvider->level = $freshProvider->subscriptionPlan ? $freshProvider->subscriptionPlan->name : 'FREE';
            $freshProvider->max_categories = $freshProvider->subscriptionPlan ? $freshProvider->subscriptionPlan->max_categories : 1;

            return response()->json([
                'message' => 'Abonnement activé avec succès !',
                'provider' => $freshProvider
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue lors de l\'activation.'], 500);
        }
    }
}
