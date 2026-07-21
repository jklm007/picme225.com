<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketplaceListing;
use App\Models\RentalBooking;

class RentalController extends Controller
{
    /**
     * Display a listing of the rental vehicles.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vehicles = MarketplaceListing::where('type', 'RENTAL')
            ->where('status', 'ACTIVE')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('location', compact('vehicles'));
    }

    /**
     * Store a new booking (optional, if we want to log requests before WhatsApp).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeRequest(Request $request)
    {
        // Logique pour stocker la demande si nécessaire
    }
}
