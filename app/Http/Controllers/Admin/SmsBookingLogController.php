<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OfflineBookingSms;

class SmsBookingLogController extends Controller
{
    /**
     * Display a listing of the offline SMS bookings with filters.
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $phone  = $request->input('phone');
        $date   = $request->input('date');

        $query = OfflineBookingSms::with(['userRequest', 'provider'])->orderBy('id', 'desc');

        if (!empty($status)) {
            $query->where('status', $status);
        }
        
        if (!empty($phone)) {
            $query->where('provider_phone', 'like', "%{$phone}%");
        }
        
        if (!empty($date)) {
            $query->whereDate('created_at', $date);
        }

        $bookings = $query->paginate(30);

        // Calculate some basic stats
        $stats = [
            'total'    => OfflineBookingSms::count(),
            'pending'  => OfflineBookingSms::where('status', 'PENDING')->count(),
            'accepted' => OfflineBookingSms::where('status', 'ACCEPTED')->count(),
            'expired'  => OfflineBookingSms::where('status', 'EXPIRED')->count(),
        ];

        return view('admin.sms-booking.index', compact('bookings', 'stats'));
    }
}
