<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserRequests;
use Illuminate\Http\Request;

class DigitalTicketController extends Controller
{
    /**
     * Display the digital ticket to the customer.
     */
    public function show($booking_id)
    {
        $booking = UserRequests::where('booking_id', $booking_id)
            ->with(['user', 'payment', 'ticket'])
            ->first();

        if (!$booking) {
            // Check if it's a marketplace transport ticket
            $ticket = \App\Models\TransportTicket::where('qr_code', $booking_id)->first();
            if ($ticket) {
                return redirect()->route('ticket.public', ['signature' => $booking_id]);
            }
            abort(404, 'Page non trouvée.');
        }

        // Data for QR Code validation
        $qrData = json_encode([
            'id' => $booking->id,
            'booking_id' => $booking->booking_id,
            'check' => sha1($booking->booking_id . config('app.key'))
        ]);

        return view('tickets.digital', compact('booking', 'qrData'));
    }
}
