<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\SubscriptionPlan;
use App\Models\WalletPassbook;
use App\Http\Controllers\SendPushNotification;

/**
 * MarketplaceSubscriptionController
 *
 * Manages FIXED-PRICE subscription plans for the Marketplace module.
 * These plans allow sellers/merchants to publish listings, products, services, ads.
 *
 * Plans are stored in `subscription_plans` with target = 'marketplace'.
 * Subscriptions are tracked on `users.marketplace_plan_id` and `users.marketplace_plan_expires_at`.
 *
 * Available tiers (seeded by MarketplacePlansSeeder):
 *  - GRATUIT   : Free tier (0 CFA, limited categories)
 *  - STARTER   : 2 500 CFA/month (basic)
 *  - PRO       : 8 000 CFA/month (advanced features)
 *  - BUSINESS  : 20 000 CFA/month (unlimited)
 */
class MarketplaceSubscriptionController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // 1. LIST PLANS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /api/user/marketplace/subscription/plans
     */
    public function plans(Request $request)
    {
        $user = Auth::user();

        $plans = SubscriptionPlan::where('target', 'marketplace')
            ->where('status', 1)
            ->orderBy('price', 'asc')
            ->get();

        $currentPlanId = $user?->marketplace_plan_id;

        $plans->transform(function ($plan) use ($currentPlanId) {
            $plan->is_current = ($plan->id === $currentPlanId);
            return $plan;
        });

        return response()->json([
            'success'      => true,
            'data'         => $plans,
            'current_plan' => $user ? $user->load('marketplacePlan')->marketplacePlan : null,
            'expires_at'   => $user?->marketplace_plan_expires_at,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. PURCHASE PLAN
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /api/user/marketplace/subscription/purchase
     */
    public function purchase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|integer|exists:subscription_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $plan = SubscriptionPlan::where('target', 'marketplace')
            ->where('status', 1)
            ->where('id', $request->plan_id)
            ->first();

        if (! $plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan Marketplace invalide ou inactif.',
            ], 400);
        }

        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        try {
            DB::transaction(function () use ($user, $plan) {
                $lockedUser = \App\Models\User::where('id', $user->id)->lockForUpdate()->first();

                // Free plan: no charge needed
                if ($plan->price > 0) {
                    if ($lockedUser->wallet_balance < $plan->price) {
                        throw new \Exception(
                            "Solde insuffisant. Il vous faut {$plan->price} CFA dans votre portefeuille."
                        );
                    }
                    $lockedUser->wallet_balance -= $plan->price;
                }

                // Calculate expiry (cumulative if same plan still active)
                $days = match($plan->period) {
                    'DAILY'   => 1,
                    'WEEKLY'  => 7,
                    'YEARLY'  => 365,
                    default   => 30, // MONTHLY
                };

                $sameActivePlan = $lockedUser->marketplace_plan_id === $plan->id
                    && $lockedUser->marketplace_plan_expires_at
                    && Carbon::parse($lockedUser->marketplace_plan_expires_at)->gt(Carbon::now());

                $lockedUser->marketplace_plan_id = $plan->id;
                $lockedUser->marketplace_plan_expires_at = $sameActivePlan
                    ? Carbon::parse($lockedUser->marketplace_plan_expires_at)->addDays($days)
                    : Carbon::now()->addDays($days);

                $lockedUser->save();

                // Passbook entry
                if ($plan->price > 0) {
                    WalletPassbook::create([
                        'user_id'     => $lockedUser->id,
                        'amount'      => -$plan->price,
                        'status'      => 'DEBITED',
                        'via'         => 'MARKETPLACE_SUBSCRIPTION',
                        'description' => "Abonnement Marketplace : {$plan->name}",
                    ]);
                }

                // Blockchain sync (non-blocking)
                try {
                    if ($plan->price > 0) {
                        $web3 = new \App\Services\Web3Service();
                        $wallet = $lockedUser->wallet_address ?? '0x' . strtoupper(substr(md5($lockedUser->id), 0, 40));
                        $web3->recordSubscriptionPayment($wallet, $plan->id, $plan->price, $plan->name);
                    }
                } catch (\Exception $e) {
                    Log::warning('[MarketplaceSubscriptionController] Blockchain sync failed: ' . $e->getMessage());
                }
            });

            $user->refresh()->load('marketplacePlan');

            // Push notification
            try {
                $expiresStr = Carbon::parse($user->marketplace_plan_expires_at)->toDateString();
                (new SendPushNotification())->SubscriptionActivated($user->id, $plan->name, $expiresStr);
            } catch (\Exception $e) {
                Log::warning('[MarketplaceSubscriptionController] Push failed: ' . $e->getMessage());
            }

            return response()->json([
                'success'    => true,
                'message'    => "Plan {$plan->name} activé avec succès !",
                'data'       => [
                    'plan'       => $user->marketplacePlan,
                    'expires_at' => $user->marketplace_plan_expires_at,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('[MarketplaceSubscriptionController::purchase] ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Une erreur est survenue lors de l\'activation.',
            ], 400);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. STATUS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /api/user/marketplace/subscription/status
     */
    public function status(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $user->load('marketplacePlan');
        $isActive  = $user->hasActiveMarketplaceSubscription();
        $expiresAt = $user->marketplace_plan_expires_at;
        $daysLeft  = 0;

        if ($expiresAt) {
            $daysLeft = max(0, (int) Carbon::now()->diffInDays($expiresAt, false));
        }

        return response()->json([
            'success'    => true,
            'is_active'  => $isActive,
            'plan'       => $user->marketplacePlan,
            'expires_at' => $expiresAt,
            'days_left'  => $daysLeft,
        ]);
    }
}
