<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketValidationLog;
use App\Models\UserRequests;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TicketService
{
    /**
     * Generate a secure ticket for a ride.
     */
    public function generate(UserRequests $request)
    {
        // Check if ticket already exists
        $existingTicket = Ticket::where('user_request_id', $request->id)->first();
        if ($existingTicket) {
            return $existingTicket;
        }

        $token = (string) Str::uuid();
        $expiresAt = Carbon::now()->addHours(24); // Configurable TTL

        // Create signature payload
        $payload = $request->id . '|' . $request->user_id . '|' . $token . '|' . $expiresAt->timestamp;
        $secret = config('app.ticket_secret', env('APP_KEY')); // Use APP_KEY if specific secret not set
        $signature = hash_hmac('sha256', $payload, $secret);

        $ticket = Ticket::create([
            'user_request_id' => $request->id,
            'user_id' => $request->user_id,
            'token' => $token,
            'signature' => $signature,
            'status' => 'PENDING',
            'expires_at' => $expiresAt,
            'qr_code_data' => json_encode([
                't' => $token,
                's' => $signature,
                'e' => $expiresAt->timestamp,
                'r' => $request->id
            ])
        ]);

        return $ticket;
    }

    /**
     * Validate a ticket scan.
     */
    public function validate($token, $signature, $scannerType, $scannerId, $metadata = [])
    {
        $ticket = Ticket::where('token', $token)->first();

        if (!$ticket) {
            return $this->logValidation(null, $scannerType, $scannerId, 'NOT_FOUND', $metadata);
        }

        // 1. Check Status
        if ($ticket->status !== 'PENDING') {
            return $this->logValidation($ticket, $scannerType, $scannerId, 'ALREADY_USED_OR_CANCELLED', $metadata);
        }

        // 2. Check Expiration
        if ($ticket->expires_at->isPast()) {
            $ticket->update(['status' => 'EXPIRED']);
            return $this->logValidation($ticket, $scannerType, $scannerId, 'EXPIRED', $metadata);
        }

        // 3. Verify Signature
        // Reconstruct payload
        $payload = $ticket->user_request_id . '|' . $ticket->user_id . '|' . $ticket->token . '|' . $ticket->expires_at->timestamp;
        $secret = config('app.ticket_secret', env('APP_KEY'));
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            return $this->logValidation($ticket, $scannerType, $scannerId, 'INVALID_SIGNATURE', $metadata);
        }

        // 4. Success - Mark as Validated
        $ticket->update([
            'status' => 'VALIDATED',
            'validated_at' => Carbon::now(),
            'validated_by_type' => $scannerType,
            'validated_by_id' => $scannerId
        ]);

        // --- Commission au Partenaire (Station Agent) sur scan du ticket ---
        if ($scannerType === 'station_agent') {
            try {
                // 1. Résolution prioritaire via le nouveau système Partner
                $partner = \App\Models\Partner::where('id', $scannerId)
                    ->where('type', 'STATION_AGENT')
                    ->with('user')
                    ->first();

                if ($partner && $partner->user) {
                    $amount = (float) $partner->getCommissionRule('passenger_scan_cfa', 50);
                    $partnerUser = $partner->user;
                    $partnerUser->increment('wallet_balance', $amount);

                    // Log via wallet_passbooks unifié
                    \App\Models\WalletPassbook::create([
                        'user_id'     => $partnerUser->id,
                        'partner_id'  => $partner->id,
                        'amount'      => $amount,
                        'status'      => 'CREDITED',
                        'via'         => 'AGENT_PASSENGER_SCAN',
                        'description' => "Commission scan passager (Trajet #{$ticket->user_request_id})",
                        'reference_id' => (string) $ticket->user_request_id,
                    ]);
                } else {
                    // Fallback : legacy StationAgent (double-écriture pendant la période de transition)
                    $agent = \App\Models\StationAgent::find($scannerId);
                    if ($agent) {
                        $amount = $agent->commission_per_passenger;
                        $agent->increment('wallet_balance', $amount);

                        \DB::table('agent_commission_logs')->insert([
                            'station_agent_id' => $agent->id,
                            'type'             => 'PASSENGER',
                            'amount'           => $amount,
                            'reference_id'     => $ticket->user_request_id,
                            'description'      => "Commission passager (Trajet #{$ticket->user_request_id}) [legacy]",
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to award commission to Agent {$scannerId}: " . $e->getMessage());
            }
        }

        $this->logValidation($ticket, $scannerType, $scannerId, 'SUCCESS', $metadata);

        return [
            'success' => true,
            'ticket' => $ticket,
            'message' => 'Ticket validé ! Commission créditée.'
        ];
    }

    private function logValidation($ticket, $type, $id, $status, $metadata)
    {
        TicketValidationLog::create([
            'ticket_id' => $ticket ? $ticket->id : null,
            'scanned_by_type' => $type,
            'scanned_by_id' => $id,
            'status' => $status,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata
        ]);

        return [
            'success' => $status === 'SUCCESS',
            'error' => $status !== 'SUCCESS' ? $status : null,
            'ticket' => $ticket
        ];
    }
}
