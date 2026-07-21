<?php

namespace App\Http\Controllers\Api;

use App\Models\PackageRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StationAgent;
use Auth;
use Log;

class StationAgentController extends Controller
{
    /**
     * Get packages at the agent's station.
     */
    public function dashboard(Request $request)
    {
        $agent = StationAgent::where('user_id', Auth::user()->id)->firstOrFail();

        $pendingPickup = PackageRequest::where('pickup_station_id', $agent->pdp_stop_id)
            ->whereIn('status', ['CREATED', 'PENDING_PICKUP'])
            ->get();

        $atStation = PackageRequest::where('pickup_station_id', $agent->pdp_stop_id)
            ->where('status', 'DEPOSITED')
            ->get();

        $readyForPickup = PackageRequest::where('dropoff_station_id', $agent->pdp_stop_id)
            ->where('status', 'ARRIVED')
            ->get();

        return response()->json([
            'station' => $agent->station,
            'pending_pickup' => $pendingPickup,
            'at_station' => $atStation,
            'ready_for_pickup_by_recipient' => $readyForPickup
        ]);
    }

    /**
     * Scan and receive a package at the station.
     */
    public function receivePackage(Request $request)
    {
        $this->validate($request, [
            'tracking_code' => 'required'
        ]);

        $agent = StationAgent::where('user_id', Auth::user()->id)->firstOrFail();
        $package = PackageRequest::where('tracking_code', $request->tracking_code)->firstOrFail();

        // Security: Ensure the package is assigned to this company
        if ($package->interurban_company_id != $agent->interurban_company_id) {
            return response()->json(['error' => 'Ce colis appartient à une autre compagnie.'], 403);
        }

        $package->status = 'DEPOSITED';
        $package->pickup_station_id = $agent->pdp_stop_id; // Set actual pickup station
        $package->save();

        return response()->json([
            'message' => 'Colis réceptionné avec succès à ' . $agent->station->name,
            'package' => $package
        ]);
    }

    /**
     * Mark package as in transit (loaded in bus).
     */
    public function shipPackage(Request $request)
    {
        $this->validate($request, [
            'tracking_code' => 'required'
        ]);

        $package = PackageRequest::where('tracking_code', $request->tracking_code)->firstOrFail();
        $package->status = 'IN_TRANSIT';
        $package->save();

        return response()->json(['message' => 'Colis marqué en transit.', 'package' => $package]);
    }

    /**
     * Mark package as arrived at target station.
     */
    public function arrivalAtDestination(Request $request)
    {
        $this->validate($request, [
            'tracking_code' => 'required'
        ]);

        $agent = StationAgent::where('user_id', Auth::user()->id)->firstOrFail();
        $package = PackageRequest::where('tracking_code', $request->tracking_code)->firstOrFail();

        $package->status = 'ARRIVED';
        $package->dropoff_station_id = $agent->pdp_stop_id;
        $package->save();

        // Send Notification to Recipient
        // (New Notification logic could be added here)

        return response()->json(['message' => 'Colis arrivé à destination.', 'package' => $package]);
    }

    /**
     * Final delivery to recipient via OTP.
     */
    public function releaseToRecipient(Request $request)
    {
        $this->validate($request, [
            'tracking_code' => 'required',
            'otp' => 'required'
        ]);

        $package = PackageRequest::where('tracking_code', $request->tracking_code)->firstOrFail();

        if ($request->otp != $package->otp_pickup) { // We reuse otp_pickup for the final release
            return response()->json(['error' => 'Code OTP de retrait invalide.'], 400);
        }

        $package->status = 'DELIVERED';
        $package->save();

        return response()->json(['message' => 'Colis remis au destinataire. Livraison terminée.', 'package' => $package]);
    }
}
