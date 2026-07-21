<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\UserSubscriptionSchedule;
use App\Models\ServiceType;
use App\Services\SubscriptionPricingService;
use App\Http\Controllers\SendPushNotification;

/**
 * UserSubscriptionController
 *
 * Handles DYNAMIC transport/commute subscription scheduling.
 *
 * Architecture:
 *  - NO fixed-price transport plans. All prices are calculated in real-time.
 *  - Each subscription is a UserSubscriptionSchedule record with:
 *      · OSRM-calculated distance & duration
 *      · Dynamic monthly price
 *      · Validity period (expires_at)
 *
 * @see App\Http\Controllers\MarketplaceSubscriptionController for fixed Marketplace plans.
 */
class UserSubscriptionController extends Controller
{
    public function __construct(private SubscriptionPricingService $pricingService)
    {}

    // ─────────────────────────────────────────────────────────────────────────
    // 1. ESTIMATION (Public endpoint — no purchase)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Estimate the dynamic monthly price for a commute subscription.
     *
     * POST /api/user/subscription/estimate
     */
    public function estimate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_type_id' => 'required|integer|exists:service_types,id',
            's_lat'           => 'required|numeric|between:-90,90',
            's_lng'           => 'required|numeric|between:-180,180',
            'd_lat'           => 'required|numeric|between:-90,90',
            'd_lng'           => 'required|numeric|between:-180,180',
            'active_days'     => 'required|array|min:1|max:7',
            'active_days.*'   => 'required|string|in:MON,TUE,WED,THU,FRI,SAT,SUN',
            'return_time'     => 'nullable|date_format:H:i',
            'waypoints'       => 'nullable|array|max:5',
            'waypoints.*.latitude'  => 'required_with:waypoints|numeric|between:-90,90',
            'waypoints.*.longitude' => 'required_with:waypoints|numeric|between:-180,180',
            'waypoints.*.address'   => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $waypoints = $request->waypoints ?? null;
            $result = $this->pricingService->calculate(
                serviceTypeId:  (int) $request->service_type_id,
                sLat:           (float) $request->s_lat,
                sLng:           (float) $request->s_lng,
                dLat:           (float) $request->d_lat,
                dLng:           (float) $request->d_lng,
                activeDays:     $request->active_days,
                returnTime:     $request->return_time,
                discountPercent: null, // use admin-configured default
                waypoints:      $waypoints
            );

            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('[UserSubscriptionController::estimate] ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul du tarif.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. PURCHASE — Create & activate a commute schedule
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Purchase and activate a new commute subscription schedule.
     *
     * POST /api/user/subscription/purchase
     */
    public function purchase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_type_id' => 'required|integer|exists:service_types,id',
            's_address'       => 'required|string|max:255',
            's_lat'           => 'required|numeric|between:-90,90',
            's_lng'           => 'required|numeric|between:-180,180',
            'd_address'       => 'required|string|max:255',
            'd_lat'           => 'required|numeric|between:-90,90',
            'd_lng'           => 'required|numeric|between:-180,180',
            'pickup_time'     => 'required|date_format:H:i',
            'return_time'     => 'nullable|date_format:H:i',
            'active_days'     => 'required|array|min:1|max:7',
            'active_days.*'   => 'required|string|in:MON,TUE,WED,THU,FRI,SAT,SUN',
            // The client must send back the estimated price (validated server-side below)
            'quoted_price'    => 'required|numeric|min:0',
            'waypoints'       => 'nullable|array|max:5',
            'waypoints.*.latitude'  => 'required_with:waypoints|numeric|between:-90,90',
            'waypoints.*.longitude' => 'required_with:waypoints|numeric|between:-180,180',
            'waypoints.*.address'   => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        try {
            // ── 1. Re-compute price server-side (never trust client price alone) ──
            $waypoints = $request->waypoints ?? null;
            $pricing = $this->pricingService->calculate(
                serviceTypeId:  (int) $request->service_type_id,
                sLat:           (float) $request->s_lat,
                sLng:           (float) $request->s_lng,
                dLat:           (float) $request->d_lat,
                dLng:           (float) $request->d_lng,
                activeDays:     $request->active_days,
                returnTime:     $request->return_time,
                waypoints:      $waypoints
            );

            $finalPrice = $pricing['discounted_monthly_price'];

            // ── 2. Tolerance check: allow ±5% between quote and server price ──
            $quotedPrice = (float) $request->quoted_price;
            $tolerance   = $finalPrice * 0.05;
            if (abs($quotedPrice - $finalPrice) > $tolerance) {
                return response()->json([
                    'success'       => false,
                    'message'       => 'Le tarif a changé depuis votre estimation. Veuillez relancer le calcul.',
                    'server_price'  => $finalPrice,
                    'quoted_price'  => $quotedPrice,
                ], 409);
            }

            $schedule = DB::transaction(function () use ($user, $request, $pricing, $finalPrice) {
                // ── 3. Lock user row and check wallet balance ──────────────────
                $lockedUser = \App\Models\User::where('id', $user->id)->lockForUpdate()->first();

                if ($lockedUser->wallet_balance < $finalPrice) {
                    throw new \Exception(
                        "Solde insuffisant. Il vous faut {$finalPrice} CFA dans votre portefeuille."
                    );
                }

                // ── 4. Debit wallet ────────────────────────────────────────────
                $lockedUser->wallet_balance -= $finalPrice;
                $lockedUser->save();

                // ── 5. Deactivate any existing ACTIVE schedule (one at a time) ─
                UserSubscriptionSchedule::where('user_id', $lockedUser->id)
                    ->where('status', 'ACTIVE')
                    ->update(['status' => 'SUPERSEDED']);

                // ── 6. Create new schedule ─────────────────────────────────────
                $period = (int) \Setting::get('subscription_period_days', 30);

                $schedule = UserSubscriptionSchedule::create([
                    'user_id'      => $lockedUser->id,
                    'service_id'   => $request->service_type_id,
                    's_address'    => $request->s_address,
                    's_lat'        => $request->s_lat,
                    's_lng'        => $request->s_lng,
                    'd_address'    => $request->d_address,
                    'd_lat'        => $request->d_lat,
                    'd_lng'        => $request->d_lng,
                    'waypoints'    => $waypoints,
                    'pickup_time'  => $request->pickup_time,
                    'return_time'  => $request->return_time,
                    'active_days'  => $request->active_days,
                    // Dynamic pricing data
                    'distance_km'  => $pricing['distance_km'],
                    'duration_mins'=> $pricing['duration_mins'],
                    'monthly_price'=> $finalPrice,
                    'payment_mode' => 'WALLET',
                    'expires_at'   => Carbon::now()->addDays($period),
                    'status'       => 'ACTIVE',
                ]);

                // ── 7. Wallet passbook entry ───────────────────────────────────
                \App\Models\WalletPassbook::create([
                    'user_id'     => $lockedUser->id,
                    'amount'      => -$finalPrice,
                    'status'      => 'DEBITED',
                    'via'         => 'TRANSPORT_SUBSCRIPTION',
                    'description' => "Abonnement trajet récurrent #{$schedule->id} · {$request->s_address} → {$request->d_address}",
                ]);

                return $schedule;
            });

            // ── 8. Push notification ───────────────────────────────────────────
            try {
                $expiresStr = $schedule->expires_at->toDateString();
                (new SendPushNotification())->SubscriptionActivated(
                    $user->id,
                    "Pass Trajet Récurrent",
                    $expiresStr
                );
            } catch (\Exception $e) {
                Log::warning('[UserSubscriptionController] Push notification failed: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Abonnement trajet activé avec succès !',
                'data'    => [
                    'schedule'        => $schedule,
                    'price_paid'      => $finalPrice,
                    'expires_at'      => $schedule->expires_at->toDateTimeString(),
                    'days_remaining'  => $schedule->daysRemaining(),
                    'routing_method'  => $pricing['routing_method'],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('[UserSubscriptionController::purchase] ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Une erreur est survenue lors de l\'activation.',
            ], 400);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. STATUS — Get current active schedule
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /api/user/subscription/status
     */
    public function status(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        // Active transport schedule
        $schedule = UserSubscriptionSchedule::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->orderBy('created_at', 'desc')
            ->first();

        // Active marketplace plan
        $marketplaceActive = $user->hasActiveMarketplaceSubscription();
        $user->load('marketplacePlan');

        return response()->json([
            'success'            => true,
            // Transport scheduling
            'transport'          => [
                'has_active_schedule' => (bool) $schedule?->isValid(),
                'schedule'            => $schedule,
                'days_remaining'      => $schedule?->daysRemaining() ?? 0,
                'expires_at'          => $schedule?->expires_at?->toDateTimeString(),
            ],
            // Marketplace subscription
            'marketplace'        => [
                'is_active'    => $marketplaceActive,
                'plan'         => $user->marketplacePlan,
                'expires_at'   => $user->marketplace_plan_expires_at,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. SCHEDULE — Modify the itinerary of an existing active schedule
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /api/user/subscription/schedule
     */
    public function getSchedule(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $schedule = UserSubscriptionSchedule::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun abonnement trajet actif trouvé.',
            ], 404);
        }

        return response()->json(['success' => true, 'data' => $schedule]);
    }

    /**
     * POST /api/user/subscription/schedule  (update itinerary on active schedule)
     * Only allows updating times & days — the price is NOT recalculated (no extra charge).
     * To change origin/destination, a new subscription must be purchased.
     */
    public function updateSchedule(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $schedule = UserSubscriptionSchedule::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $schedule || ! $schedule->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun abonnement trajet actif. Souscrivez d\'abord un abonnement.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'pickup_time'   => 'sometimes|date_format:H:i',
            'return_time'   => 'nullable|date_format:H:i',
            'active_days'   => 'sometimes|array|min:1|max:7',
            'active_days.*' => 'required|string|in:MON,TUE,WED,THU,FRI,SAT,SUN',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $schedule->fill($request->only(['pickup_time', 'return_time', 'active_days']));
        $schedule->save();

        return response()->json([
            'success' => true,
            'message' => 'Planning mis à jour avec succès.',
            'data'    => $schedule,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. CANCEL — Cancel an active commute schedule
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /api/user/subscription/cancel
     * No refund is issued (non-refundable like a transport pass).
     */
    public function cancel(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $schedule = UserSubscriptionSchedule::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $schedule) {
            return response()->json(['success' => false, 'message' => 'Aucun abonnement actif à annuler.'], 404);
        }

        $schedule->status = 'CANCELED';
        $schedule->save();

        return response()->json([
            'success' => true,
            'message' => 'Abonnement trajet annulé. Les courses planifiées à venir ne seront plus générées.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. HISTORY — Past schedules
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /api/user/subscription/history
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $schedules = UserSubscriptionSchedule::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json(['success' => true, 'data' => $schedules]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DEPRECATED — kept for backward-compatibility during transition
    // ─────────────────────────────────────────────────────────────────────────

    /** @deprecated Use estimate() instead */
    public function estimateCustomSubscription(Request $request)
    {
        $request->merge(['service_type_id' => $request->input('service_id')]);
        return $this->estimate($request);
    }

    /** @deprecated Use purchase() instead */
    public function purchaseSubscription(Request $request)
    {
        Log::warning('[UserSubscriptionController] Deprecated purchaseSubscription() called. Redirecting to purchase().');
        return $this->purchase($request);
    }

    /** @deprecated Use status() instead */
    public function getSubscriptionStatus(Request $request)
    {
        return $this->status($request);
    }

    /** @deprecated Use getSchedule() instead */
    public function getSubscriptionSchedule(Request $request)
    {
        return $this->getSchedule($request);
    }

    /** @deprecated Use updateSchedule() instead */
    public function saveSubscriptionSchedule(Request $request)
    {
        return $this->updateSchedule($request);
    }

    /** @deprecated — fixed plans are now only for Marketplace */
    public function getSubscriptionPlans(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Les abonnements de transport ne sont plus des plans à prix fixe. '
                       . 'Utilisez POST /api/user/subscription/estimate pour calculer un tarif dynamique. '
                       . 'Pour les abonnements Marketplace (vendeurs), utilisez GET /api/user/marketplace/subscription/plans.',
        ], 410);
    }
}
