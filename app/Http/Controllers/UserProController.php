<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserRequestPayment;

class UserProController extends Controller
{
    /**
     * Unlock PRO tier via Store In-App Purchase validation
     * 
     * POST /api/v1/user/pro/unlock
     */
    public function unlockPro(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_type'     => 'required|in:google_play,apple_appstore',
            'product_id'     => 'required|string',
            'purchase_token' => 'required|string',
            'transaction_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        if (!$user) {
            // Fallback for different guards
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            }
        }

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        // 1. Prevent Replay Attacks
        $exists = DB::table('store_receipts')
            ->where('transaction_id', $request->transaction_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'error'   => 'Ce reçu d\'achat ou ID de transaction a déjà été utilisé.'
            ], 422);
        }

        // 2. Cryptographic Validation (Simulated in Beta, configurable validation)
        // In a real production system, this would call Google Play Developer API / App Store API.
        // For zero-downtime integration, we simulate success and save the receipt details.
        
        DB::transaction(function () use ($user, $request) {
            // Write to receipts log
            DB::table('store_receipts')->insert([
                'user_id'        => $user->id,
                'product_id'     => $request->product_id,
                'store_type'     => $request->store_type,
                'purchase_token' => $request->purchase_token,
                'transaction_id' => $request->transaction_id,
                'purchase_date'  => now(),
                'status'         => 'VALIDATED',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // Update user status
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'member_tier'     => 'PRO',
                    'pro_unlocked_at' => now(),
                    'updated_at'      => now(),
                ]);
        });

        // Get fresh user instance
        $freshUser = DB::table('users')->where('id', $user->id)->first();

        return response()->json([
            'success'         => true,
            'message'         => 'Niveau PRO déverrouillé avec succès.',
            'member_tier'     => $freshUser->member_tier,
            'pro_unlocked_at' => $freshUser->pro_unlocked_at,
        ]);
    }

    /**
     * Update Global Application Settings & Modes (Admin only)
     * 
     * POST /api/v1/admin/settings/modes
     */
    public function updateModes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_mode'             => 'required|in:BETA,COMMERCIAL',
            'beta_mode'            => 'required|boolean',
            'commission_enabled'   => 'required|boolean',
            'subscription_enabled' => 'required|boolean',
            'pro_enabled'          => 'required|boolean',
            'pro_price_fcfa'       => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $settings = [
            'app_mode'             => $request->app_mode,
            'beta_mode'            => $request->beta_mode ? '1' : '0',
            'commission_enabled'   => $request->commission_enabled ? '1' : '0',
            'subscription_enabled' => $request->subscription_enabled ? '1' : '0',
            'pro_enabled'          => $request->pro_enabled ? '1' : '0',
            'pro_price_fcfa'       => $request->pro_price_fcfa,
        ];

        foreach ($settings as $key => $val) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $val, 'updated_at' => now()]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Modes applicatifs mis à jour avec succès.',
            'settings'=> $settings
        ]);
    }

    /**
     * Retrieve Real vs Simulated Financial statistics (Admin only)
     * 
     * GET /api/v1/admin/stats/business
     */
    public function getStats(Request $request)
    {
        // 1. Real Financials
        $realRevenue = (float) DB::table('user_request_payments')->sum('total');
        $realCommission = (float) DB::table('user_request_payments')->sum('commision');
        $realProviderCommission = (float) DB::table('user_request_payments')->sum('provider_commission');
        
        $activeSubCount = DB::table('user_subscription_schedules')
            ->where('status', 'ACTIVE')
            ->where('expires_at', '>', now())
            ->count();

        // 2. Simulated Financials (Beta Mode)
        $simulatedRevenue = (float) DB::table('simulated_commission_logs')->sum('total_amount');
        $simulatedCommission = (float) DB::table('simulated_commission_logs')->sum('simulated_commission');

        // 3. PRO User Metrics
        $totalUsers = DB::table('users')->count();
        $proUsers = DB::table('users')->where('member_tier', 'PRO')->count();
        $conversionRate = $totalUsers > 0 ? round(($proUsers / $totalUsers) * 100, 2) : 0.00;

        // 4. Revenue by service type
        $revenueByService = DB::table('user_requests as ur')
            ->join('user_request_payments as urp', 'ur.id', '=', 'urp.request_id')
            ->join('service_types as st', 'ur.service_type_id', '=', 'st.id')
            ->select('st.name as service_name', DB::raw('SUM(urp.total) as total_revenue'))
            ->groupBy('st.id', 'st.name')
            ->get();

        // 5. Revenue by city/commune (based on provider's registered commune)
        $revenueByCommune = DB::table('user_requests as ur')
            ->join('user_request_payments as urp', 'ur.id', '=', 'urp.request_id')
            ->join('providers as p', 'ur.provider_id', '=', 'p.id')
            ->select(DB::raw('COALESCE(NULLIF(p.commune, \'\'), \'Hors Commune\') as commune_name'), DB::raw('SUM(urp.total) as total_revenue'))
            ->groupBy('p.commune')
            ->get();

        return response()->json([
            'success' => true,
            'real' => [
                'total_revenue'         => $realRevenue,
                'customer_commissions'  => $realCommission,
                'provider_commissions'  => $realProviderCommission,
                'active_subscriptions'  => $activeSubCount,
            ],
            'simulated' => [
                'total_revenue' => $simulatedRevenue,
                'commissions'   => $simulatedCommission,
            ],
            'pro_metrics' => [
                'total_users'     => $totalUsers,
                'pro_users'       => $proUsers,
                'conversion_rate' => $conversionRate,
            ],
            'revenue_by_service' => $revenueByService,
            'revenue_by_commune' => $revenueByCommune
        ]);
    }
}
