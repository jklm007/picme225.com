<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserRequests;
use App\Models\Ticket;
use App\Models\Partner;
use App\Models\WalletPassbook;
use App\Services\TicketService;
use Auth;
use Log;
use DB;

class CashSaleController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Create a cash booking at the station.
     * Agent creates reservation for walk-in customer.
     */
    public function createCashBooking(Request $request)
    {
        $this->validate($request, [
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'pdp_route_id' => 'required|integer',
            'pickup_stop_id' => 'required|integer',
            'dropoff_stop_id' => 'required|integer',
            'service_type_id' => 'required|integer',
            'seats' => 'required|integer|min:1',
            'total_amount' => 'required|numeric',
            'departure_time' => 'nullable|date',
        ]);

        // Résolution du partenaire (prioritaire) ou de l'agent legacy
        $user    = Auth::user();
        $partner = $user ? Partner::where('user_id', $user->id)->where('type', 'STATION_AGENT')->with('user')->first() : null;
        $agent   = $user ? $user->stationAgent : null;

        if (!$partner && !$agent) {
            return response()->json(['error' => 'Unauthorized. Not a station agent.'], 403);
        }

        try {
            DB::beginTransaction();

            // 1. Create or find customer user (anonymous cash customer)
            $customer = \App\Models\User::firstOrCreate(
                ['mobile' => $request->customer_phone],
                [
                    'first_name' => $request->customer_name,
                    'last_name' => '',
                    'email' => 'cash_' . time() . '@station.local',
                    'password' => bcrypt(str_random(16)),
                    'user_type' => 'USER'
                ]
            );

            // 2. Create UserRequest
            $userRequest = UserRequests::create([
                'booking_id' => 'CASH-' . strtoupper(uniqid()),
                'user_id' => $customer->id,
                'provider_id' => null, // Will be assigned later
                'current_provider_id' => null,
                'service_type_id' => $request->service_type_id,
                'status' => 'PENDING',
                'cancelled_by' => null,
                's_latitude' => $request->s_latitude ?? 0,
                's_longitude' => $request->s_longitude ?? 0,
                'd_latitude' => $request->d_latitude ?? 0,
                'd_longitude' => $request->d_longitude ?? 0,
                's_address' => $request->pickup_stop_name ?? 'Station',
                'd_address' => $request->dropoff_stop_name ?? 'Destination',
                'distance' => $request->distance ?? 0,
                'payment_mode' => 'CASH',
                'is_paid' => 1, // Cash already collected
                'paid_at' => now(),
                'is_scheduled' => $request->departure_time ? 1 : 0,
                'schedule_at' => $request->departure_time,
                'pdp_route_id' => $request->pdp_route_id,
                'pickup_stop_id' => $request->pickup_stop_id,
                'dropoff_stop_id' => $request->dropoff_stop_id,
                'seat_count' => $request->seats,
                'created_by_agent' => $agent->id,
            ]);

            // 3. Create Payment Record
            \App\Models\UserRequestPayment::create([
                'request_id' => $userRequest->id,
                'user_id' => $customer->id,
                'payment_id' => 'CASH_' . time(),
                'payment_mode' => 'CASH',
                'fixed' => $request->total_amount,
                'distance' => 0,
                'commision' => 0,
                'discount' => 0,
                'tax' => 0,
                'wallet' => 0,
                'surge' => 0,
                'total' => $request->total_amount,
                'payable' => $request->total_amount,
                'provider_pay' => 0,
            ]);

            // 4. Generate Ticket (for tracking even if cash)
            $ticket = $this->ticketService->generate($userRequest);

            // 5. Mark as VALIDATED immediately (no need to scan again)
            $ticket->update([
                'status' => 'VALIDATED',
                'validated_at' => now(),
                'validated_by_type' => 'station_agent',
                'validated_by_id' => $agent->id
            ]);

            // 6. Safety Check & Wallet Logic for Fleet Owner
            if ($agent->company && $agent->company->fleet) {
                $fleet = $agent->company->fleet;
                $prepaidService = new \App\Services\FleetPrepaidService();

                // Centralized check for Mode (Managed/Autonomous) and Debt (2-3 months)
                $canProceed = $prepaidService->canPerformOperation($fleet, $request->total_amount);

                if (!$canProceed['allowed']) {
                    DB::rollBack();
                    return response()->json([
                        'error' => $canProceed['reason'],
                        'current_balance' => $fleet->prepaid_balance,
                        'required' => $request->total_amount
                    ], 402); // 402 Payment Required
                }

                // If check passed:
                // For MANAGED fleets or RESTRICTED autonomous fleets, we must deduct from prepaid balance
                if ($fleet->financial_mode === 'MANAGED' || $fleet->unpaid_months_count >= 2) {
                    $deductResult = $prepaidService->deduct(
                        $fleet,
                        $request->total_amount,
                        $userRequest->booking_id,
                        "Vente cash: {$request->customer_name} → {$request->dropoff_stop_name}"
                    );

                    if (!$deductResult['success']) {
                        DB::rollBack();
                        return response()->json(['error' => $deductResult['error']], 400);
                    }
                }

                // Attribution de la commission
                if ($partner && $partner->user) {
                    $amount = (float) $partner->getCommissionRule('passenger_scan_cfa', 50);
                    $partner->user->increment('wallet_balance', $amount);

                    WalletPassbook::create([
                        'user_id'      => $partner->user->id,
                        'partner_id'   => $partner->id,
                        'amount'       => $amount,
                        'status'       => 'CREDITED',
                        'via'          => 'CASH_SALE_PASSENGER',
                        'description'  => "Vente Cash Agent: {$partner->user->first_name} (Trajet #{$userRequest->booking_id})",
                        'reference_id' => (string) $userRequest->id,
                    ]);
                } elseif ($agent) {
                    $amount = $agent->commission_per_passenger;
                    $fleet->increment('wallet_balance', $amount);

                    DB::table('agent_commission_logs')->insert([
                        'station_agent_id' => $agent->id,
                        'fleet_id'         => $fleet->id,
                        'type'             => 'PASSENGER',
                        'amount'           => $amount,
                        'reference_id'     => $userRequest->id,
                        'description'      => "Vente Cash Agent: {$agent->user->first_name} (Trajet #{$userRequest->booking_id}) [legacy]",
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }

            // 7. Log cash collection for reconciliation
            DB::table('cash_collections')->insert([
                'agent_id' => $agent->id,
                'request_id' => $userRequest->id,
                'amount' => $request->total_amount,
                'collected_at' => now(),
                'reconciled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Réservation cash créée avec succès',
                'booking' => $userRequest,
                'ticket' => $ticket,
                'qr_data' => $ticket->qr_code_data,
                'cash_collected' => $request->total_amount,
                'commission_earned' => $amount ?? 0
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cash Sale Error: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la création de la réservation'], 500);
        }
    }

    /**
     * Get cash collection summary for agent.
     */
    public function cashSummary(Request $request)
    {
        $agent = Auth::user()->stationAgent;
        if (!$agent) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $today = now()->toDateString();

        $summary = DB::table('cash_collections')
            ->where('agent_id', $agent->id)
            ->whereDate('collected_at', $today)
            ->select(
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('SUM(amount) as total_cash_collected'),
                DB::raw('SUM(CASE WHEN reconciled = 1 THEN amount ELSE 0 END) as reconciled_amount'),
                DB::raw('SUM(CASE WHEN reconciled = 0 THEN amount ELSE 0 END) as pending_amount')
            )
            ->first();

        return response()->json($summary);
    }
}
