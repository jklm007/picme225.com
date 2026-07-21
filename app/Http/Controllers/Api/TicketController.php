<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TicketService;
use App\Models\UserRequests;
use Auth;
use Log;

class TicketController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Scan and validate a ticket.
     * Accessible by Providers, Dispatchers, Admin.
     */
    public function scan(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
            'signature' => 'required|string',
        ]);

        try {
            // Determine scanner type
            $scannerType = 'unknown';
            $scannerId = 0;

            if (Auth::guard('provider')->check()) {
                $scannerType = 'provider';
                $scannerId = Auth::guard('provider')->id();
            } elseif (Auth::guard('api')->check()) {
                // Antigravity: Check if user is a station agent
                $agent = \App\Models\StationAgent::where('user_id', Auth::guard('api')->id())
                    ->where('is_active', true)
                    ->first();
                if ($agent) {
                    $scannerType = 'station_agent';
                    $scannerId = $agent->id;
                } else {
                    $scannerType = 'user';
                    $scannerId = Auth::guard('api')->id();
                }
            } elseif (Auth::guard('dispatcher')->check()) {
                $scannerType = 'dispatcher';
                $scannerId = Auth::guard('dispatcher')->id();
            } elseif (Auth::guard('admin')->check()) {
                $scannerType = 'admin';
                $scannerId = Auth::guard('admin')->id();
            }

            $result = $this->ticketService->validate(
                $request->token,
                $request->signature,
                $scannerType,
                $scannerId,
                ['lat' => $request->lat, 'lng' => $request->lng]
            );

            if (!$result['success']) {
                return response()->json([
                    'error' => $result['error'],
                    'message' => 'Ticket validation failed'
                ], 400);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Ticket Scan Error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Get ticket details for a specific ride (User side).
     */
    public function show($request_id)
    {
        $ticket = \App\Models\Ticket::where('user_request_id', $request_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        return response()->json($ticket);
    }
}
