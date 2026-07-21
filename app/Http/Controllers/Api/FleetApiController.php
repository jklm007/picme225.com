<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserRequests;
use App\Models\UserRequestPayment;
use App\Models\Provider;
use App\Models\ServiceType;
use App\Models\Partner;
use App\Models\WalletPassbook;
use Auth;
use Log;

class FleetApiController extends Controller
{
    // -------------------------------------------------------------------------
    // Helpers (Partner-aware)
    // -------------------------------------------------------------------------

    /**
     * Résoudre le Partner FLEET_OWNER de l'utilisateur connecté.
     * Retourne null si le compte n'a pas encore été migré.
     */
    private function resolvePartner(): ?Partner
    {
        $user = Auth::user();
        if (!$user) return null;

        return Partner::where('user_id', $user->id)
            ->where('type', 'FLEET_OWNER')
            ->first();
    }

    /**
     * Retrouver l'objet Fleet legacy (toujours nécessaire pendant la transition).
     */
    private function resolveFleet(): ?\App\Models\Fleet
    {
        $user = Auth::user();
        if (!$user || !$user->fleet_id) return null;

        return \App\Models\Fleet::find($user->fleet_id);
    }

    // -------------------------------------------------------------------------
    // Dashboard
    // -------------------------------------------------------------------------

    /**
     * Get Fleet Owner Dashboard statistics for mobile app.
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        // Accepter aussi les comptes dont user_type === 'FLEET_OWNER' (nouveau système)
        $isLegacyFleet  = ($user->user_type === 'FLEET' && $user->fleet_id);
        $partner        = $this->resolvePartner();
        $isUnifiedFleet = ($partner !== null);

        if (!$isLegacyFleet && !$isUnifiedFleet) {
            return response()->json(['error' => 'Unauthorized. Not a Fleet Owner account.'], 403);
        }

        try {
            $fleetId = $user->fleet_id;

            // 1. Total Rides for this Fleet
            $ridesQuery = UserRequests::whereHas('provider', function ($query) use ($fleetId) {
                $query->where('fleet', $fleetId);
            });

            $totalRides     = (clone $ridesQuery)->count();
            $completedRides = (clone $ridesQuery)->where('status', 'COMPLETED')->count();
            $cancelledRides = (clone $ridesQuery)->where('status', 'CANCELLED')->count();

            // 2. Revenue Calculation
            $rideIds      = (clone $ridesQuery)->pluck('id');
            $totalRevenue = UserRequestPayment::whereIn('request_id', $rideIds)->sum('total');

            // 3. Active/Total Drivers
            // Drivers liés via Partner (nouveau système) OU via fleet_id legacy
            if ($partner) {
                $providers     = Provider::where('partner_id', $partner->id)->get();
                // Fusionner avec les chauffeurs legacy encore sous fleet_id
                $legacyDrivers = Provider::where('fleet', $fleetId)
                    ->whereNull('partner_id')
                    ->get();
                $providers     = $providers->merge($legacyDrivers);
            } else {
                $providers = Provider::where('fleet', $fleetId)->get();
            }

            $totalDrivers  = $providers->count();
            $activeDrivers = $providers->where('status', 'approved')->count();

            // 4. Fleet Company Info
            $fleetInfo = \App\Models\Fleet::with('companies')->find($fleetId);

            // 5. Agent Production — unified (wallet_passbooks) + legacy fallback
            $agentProductionUnified = [];
            $agentProductionLegacy  = [];

            if ($partner) {
                // Affiliés STATION_AGENT de ce Fleet Owner
                $agentProductionUnified = WalletPassbook::join('partners', 'wallet_passbooks.partner_id', '=', 'partners.id')
                    ->join('users', 'partners.user_id', '=', 'users.id')
                    ->where('partners.type', 'STATION_AGENT')
                    ->whereDate('wallet_passbooks.created_at', '>=', now()->subDays(30))
                    ->select(
                        'partners.id as partner_id',
                        'users.first_name',
                        'users.last_name',
                        'partners.partner_code as agent_code',
                        \DB::raw('COUNT(*) as total_scans'),
                        \DB::raw('SUM(wallet_passbooks.amount) as total_production')
                    )
                    ->groupBy('partners.id', 'users.first_name', 'users.last_name', 'partners.partner_code')
                    ->orderBy('total_production', 'desc')
                    ->limit(10)
                    ->get()
                    ->toArray();
            }

            // Toujours inclure legacy pour les agents pas encore migrés
            $agentProductionLegacy = \DB::table('agent_commission_logs')
                ->join('station_agents', 'agent_commission_logs.station_agent_id', '=', 'station_agents.id')
                ->join('users', 'station_agents.user_id', '=', 'users.id')
                ->where('agent_commission_logs.fleet_id', $fleetId)
                ->whereDate('agent_commission_logs.created_at', '>=', now()->subDays(30))
                ->select(
                    'station_agents.id as agent_id',
                    'users.first_name',
                    'users.last_name',
                    'station_agents.agent_code',
                    \DB::raw('COUNT(*) as total_scans'),
                    \DB::raw('SUM(agent_commission_logs.amount) as total_production')
                )
                ->groupBy('station_agents.id', 'users.first_name', 'users.last_name', 'station_agents.agent_code')
                ->orderBy('total_production', 'desc')
                ->limit(10)
                ->get()
                ->toArray();

            // Fusionner et retourner les 10 meilleurs
            $topAgents = collect(array_merge($agentProductionUnified, $agentProductionLegacy))
                ->sortByDesc('total_production')
                ->take(10)
                ->values();

            // 6. Solde du wallet (unifié si Partner, legacy sinon)
            $walletBalance = $partner
                ? ($user->wallet_balance ?? 0)
                : ($fleetInfo->wallet_balance ?? 0);

            return response()->json([
                'fleet_name'    => $fleetInfo ? $fleetInfo->company : ($partner->company_name ?? 'N/A'),
                'fleet_type'    => $fleetInfo ? $fleetInfo->type : ($partner->tier ?? 'STANDARD'),
                'wallet_balance' => $walletBalance,
                'partner_id'    => $partner ? $partner->id : null,
                'system'        => $partner ? 'unified' : 'legacy',
                'statistics'    => [
                    'total_rides'     => $totalRides,
                    'completed_rides' => $completedRides,
                    'cancelled_rides' => $cancelledRides,
                    'total_revenue'   => round($totalRevenue, 2),
                    'total_drivers'   => $totalDrivers,
                    'active_drivers'  => $activeDrivers,
                ],
                'companies'  => $fleetInfo ? $fleetInfo->companies : [],
                'top_agents' => $topAgents,
            ]);

        } catch (\Exception $e) {
            Log::error('Fleet API Dashboard Error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Drivers
    // -------------------------------------------------------------------------

    /**
     * List drivers belonging to this fleet.
     */
    public function drivers(Request $request)
    {
        $user    = Auth::user();
        $partner = $this->resolvePartner();

        if (!$partner && ($user->user_type !== 'FLEET' || !$user->fleet_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($partner) {
            // Chauffeurs migrés (via partner_id) + legacy non encore migrés
            $unified = Provider::where('partner_id', $partner->id)
                ->with('service.service_type')
                ->get();
            $legacy  = Provider::where('fleet', $user->fleet_id)
                ->whereNull('partner_id')
                ->with('service.service_type')
                ->get();
            $drivers = $unified->merge($legacy)->sortByDesc('id')->values();
        } else {
            $drivers = Provider::where('fleet', $user->fleet_id)
                ->with('service.service_type')
                ->orderBy('id', 'desc')
                ->get();
        }

        return response()->json($drivers);
    }

    // -------------------------------------------------------------------------
    // Withdraw
    // -------------------------------------------------------------------------

    /**
     * Request a withdrawal from the fleet / partner wallet.
     */
    public function withdraw(Request $request)
    {
        $this->validate($request, [
            'amount'         => 'required|numeric|min:100',
            'account_number' => 'required|string',
            'recipient_name' => 'nullable|string',
        ]);

        $user    = Auth::user();
        $partner = $this->resolvePartner();
        $fleet   = $this->resolveFleet();

        if (!$partner && !$fleet) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // ── Nouveau système (Partner → user wallet) ───────────────────────
            if ($partner) {
                $balance = $user->wallet_balance ?? 0;
                if ($balance < $request->amount) {
                    return response()->json(['error' => 'Solde insuffisant pour ce retrait.'], 400);
                }

                $withdrawal = \App\Models\Withdrawal::create([
                    'user_id'        => $user->id,
                    'partner_id'     => $partner->id,
                    'amount'         => $request->amount,
                    'status'         => 'PENDING',
                    'method'         => 'MOBILE_MONEY',
                    'account_number' => $request->account_number,
                    'recipient_name' => $request->recipient_name,
                ]);

                $user->decrement('wallet_balance', $request->amount);

                WalletPassbook::create([
                    'user_id'      => $user->id,
                    'partner_id'   => $partner->id,
                    'amount'       => $request->amount,
                    'status'       => 'DEBITED',
                    'via'          => 'WITHDRAWAL',
                    'description'  => 'Retrait vers ' . $request->account_number,
                    'reference_id' => 'WDR_' . $withdrawal->id,
                ]);

                return response()->json([
                    'message'     => 'Votre demande de retrait a été enregistrée avec succès.',
                    'withdrawal'  => $withdrawal,
                    'new_balance' => $user->fresh()->wallet_balance,
                    'system'      => 'unified',
                ]);
            }

            // ── Fallback legacy (Fleet wallet) ────────────────────────────────
            if ($fleet->wallet_balance < $request->amount) {
                return response()->json(['error' => 'Solde insuffisant pour ce retrait.'], 400);
            }

            $withdrawal = \App\Models\Withdrawal::create([
                'fleet_id'       => $fleet->id,
                'amount'         => $request->amount,
                'status'         => 'PENDING',
                'method'         => 'MOBILE_MONEY',
                'account_number' => $request->account_number,
                'recipient_name' => $request->recipient_name,
            ]);

            $fleet->decrement('wallet_balance', $request->amount);

            \DB::table('fleet_wallets')->insert([
                'fleet_id'         => $fleet->id,
                'amount'           => -$request->amount,
                'transaction_id'   => 'WDR_' . $withdrawal->id,
                'transaction_desc' => 'Demande de retrait vers ' . $request->account_number,
                'type'             => 'DEBIT',
                'balance'          => $fleet->wallet_balance,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            return response()->json([
                'message'     => 'Votre demande de retrait a été enregistrée avec succès.',
                'withdrawal'  => $withdrawal,
                'new_balance' => $fleet->wallet_balance,
                'system'      => 'legacy',
            ]);

        } catch (\Exception $e) {
            Log::error('Fleet Payout error: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur technique'], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Withdrawal History
    // -------------------------------------------------------------------------

    /**
     * Get withdrawal history for this fleet / partner.
     */
    public function withdrawalHistory(Request $request)
    {
        $user    = Auth::user();
        $partner = $this->resolvePartner();

        if (!$partner && ($user->user_type !== 'FLEET' || !$user->fleet_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($partner) {
            $history = \App\Models\Withdrawal::where('partner_id', $partner->id)
                ->orWhere('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $history = \App\Models\Withdrawal::where('fleet_id', $user->fleet_id)
                ->orderBy('id', 'desc')
                ->get();
        }

        return response()->json($history);
    }

    // -------------------------------------------------------------------------
    // Prepaid Recharge
    // -------------------------------------------------------------------------

    /**
     * Recharge the prepaid balance (MANAGED mode).
     */
    public function rechargePrepaid(Request $request)
    {
        $this->validate($request, [
            'amount'            => 'required|numeric|min:5000',
            'payment_method'    => 'required|string',
            'payment_reference' => 'required|string|unique:fleet_prepaid_transactions,payment_reference',
        ]);

        $user = Auth::user();
        if ($user->user_type !== 'FLEET' || !$user->fleet_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $fleet          = \App\Models\Fleet::findOrFail($user->fleet_id);
            $prepaidService = new \App\Services\FleetPrepaidService();

            $result = $prepaidService->recharge(
                $fleet,
                $request->amount,
                $request->payment_method,
                $request->payment_reference
            );

            if ($result['success']) {
                return response()->json([
                    'message'     => $result['message'],
                    'new_balance' => $result['new_balance'],
                ]);
            }

            return response()->json(['error' => $result['error']], 400);

        } catch (\Exception $e) {
            \Log::error('Fleet Recharge Error: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du traitement de la recharge'], 500);
        }
    }

    /**
     * Initialize a direct payment for recharge (Wave/Orange/MTN).
     */
    public function initRechargePayment(Request $request)
    {
        $this->validate($request, ['amount' => 'required|numeric|min:5000']);

        $user       = Auth::user();
        $paymentUrl = "https://checkout.picme225.com/pay?amount=" . $request->amount . "&fleet_id=" . $user->fleet_id;

        return response()->json([
            'payment_url' => $paymentUrl,
            'message'     => 'Lien de paiement généré',
        ]);
    }
}
