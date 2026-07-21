<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DispatcherHybridService;
use App\Services\TicketService;
use App\Models\UserRequests;
use Auth;
use Log;

class HybridAssignmentController extends Controller
{
    protected $hybridService;
    protected $ticketService;

    public function __construct(DispatcherHybridService $hybridService, TicketService $ticketService)
    {
        $this->hybridService = $hybridService;
        $this->ticketService = $ticketService;
    }

    /**
     * Manual Assignment.
     */
    public function assignManual(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|exists:user_requests,id',
            'provider_id' => 'required|exists:providers,id'
        ]);

        try {
            $dispatcherId = Auth::guard('dispatcher')->id();
            $result = $this->hybridService->assignManual($request->request_id, $request->provider_id, $dispatcherId);

            return response()->json(['message' => 'Driver assigned successfully', 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Broadcast to Drivers.
     */
    public function broadcast(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|exists:user_requests,id',
            'radius' => 'numeric|min:1'
        ]);

        try {
            $dispatcherId = Auth::guard('dispatcher')->id();
            $result = $this->hybridService->broadcastToDrivers(
                $request->request_id, 
                $dispatcherId, 
                $request->radius ?? 10
            );

            if (!$result['success']) {
                return response()->json($result, 400);
            }

            return response()->json(['message' => 'Broadcast sent successfully', 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Force Ticket Validation (Dispatcher Override).
     */
    public function forceValidation(Request $request)
    {
        $this->validate($request, [
            'ticket_token' => 'required|exists:tickets,token'
        ]);

        try {
            $ticket = \App\Models\Ticket::where('token', $request->ticket_token)->first();
            
            // Re-use service but with explicit dispatcher type
            $result = $this->ticketService->validate(
                $ticket->token,
                $ticket->signature, // We use the stored signature since we are forcing it
                'dispatcher_force',
                Auth::guard('dispatcher')->id(),
                ['reason' => $request->reason ?? 'Manual Override']
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
