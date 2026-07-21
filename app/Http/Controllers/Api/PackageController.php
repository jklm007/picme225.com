<?php

namespace App\Http\Controllers\Api;

use App\Models\PackageRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Auth;
use Log;
use Carbon\Carbon;
use App\Models\ServiceType;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    /**
     * Get a quote for delivery (Direct, Station, or Combined).
     */
    public function quote(Request $request)
    {
        $this->validate($request, [
            's_latitude' => 'required|numeric',
            's_longitude' => 'required|numeric',
            'd_latitude' => 'required|numeric',
            'd_longitude' => 'required|numeric',
            'weight' => 'numeric',
            'size_category' => 'in:S,M,L,XL'
        ]);

        $distance = Helper::haversineGreatCircleDistance(
            $request->s_latitude,
            $request->s_longitude,
            $request->d_latitude,
            $request->d_longitude
        ) / 1000;

        $options = [];

        // 1. DIRECT DELIVERY (Express Moto/Taxi)
        if ($distance <= 60) {
            $options[] = [
                'type' => 'INSTANT_DELIVERY',
                'id' => 'direct_express',
                'name' => 'Livraison Directe (Moto)',
                'price' => 1000 + ($distance * 250),
                'time_estimate' => '30-45 min',
                'description' => 'Un livreur récupère le colis et le livre DIRECTEMENT au destinataire.',
                'needs_collection' => false
            ];
        }

        // 2. STATION FREIGHT - SELF DROP (User goes to Gare)
        if ($distance > 20) {
            $options[] = [
                'type' => 'STATION_FREIGHT',
                'id' => 'station_drop',
                'name' => 'Expédition Gare à Gare',
                'price' => 500 + ($distance * 60),
                'time_estimate' => '24h',
                'description' => 'Vous déposez le colis à la gare. Le destinataire le retire à la gare d\'arrivée.',
                'needs_collection' => false
            ];

            // 3. STATION FREIGHT - DOOR TO STATION (Combined)
            $pickupFee = 800; // Fixed fee for first-mile collection
            $options[] = [
                'type' => 'STATION_FREIGHT',
                'id' => 'station_combined',
                'name' => 'Collecte à domicile + Expédition Gare',
                'price' => $pickupFee + (500 + ($distance * 60)),
                'time_estimate' => '24-36h',
                'description' => 'Un livreur vient chez vous chercher le colis et le dépose à la gare pour vous.',
                'needs_collection' => true
            ];
        }

        return response()->json([
            'distance' => round($distance, 2),
            'options' => $options
        ]);
    }

    /**
     * Create a package request.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|in:INSTANT_DELIVERY,STATION_FREIGHT',
            'id' => 'required', // direct_express, station_drop, station_combined
            'recipient_name' => 'required',
            'recipient_phone' => 'required',
            'price' => 'required|numeric'
        ]);

        try {
            $package = new PackageRequest();
            $package->fill($request->all());
            $package->user_id = Auth::user()->id;
            $package->tracking_code = 'PK-' . strtoupper(Str::random(6));
            $package->otp_pickup = mt_rand(100000, 999999);

            // Logic for Station Freight (Gare)
            if ($request->type == 'STATION_FREIGHT') {
                if ($request->has('pickup_station_id')) {
                    $station = \App\Models\PdpStop::find($request->pickup_station_id);
                    if ($station) {
                        $package->interurban_company_id = $station->interurban_company_id;
                    }
                }

                $package->needs_collection = ($request->id === 'station_combined');
                $package->status = 'CREATED';

                // --- NEW: Trigger First-Mile Collection Ride ---
                if ($package->needs_collection) {
                    $ride = new \App\Models\UserRequests();
                    $ride->booking_id = Helper::generate_booking_id();
                    $ride->user_id = Auth::user()->id;
                    $ride->current_provider_id = 0;
                    $ride->status = 'SEARCHING';
                    $ride->s_latitude = $request->s_latitude;
                    $ride->s_longitude = $request->s_longitude;
                    $ride->s_address = $request->s_address ?? 'Point de collecte';

                    if (isset($station)) {
                        $ride->d_latitude = $station->latitude;
                        $ride->d_longitude = $station->longitude;
                        $ride->d_address = $station->name;
                        $ride->dropoff_stop_id = $station->id;
                    }

                    $ride->method = 'delivery';
                    $ride->ride_variant = 'logistics';
                    $ride->otp = mt_rand(1000, 9999);
                    $ride->assigned_at = now();
                    $ride->save();

                    $package->collection_request_id = $ride->id;
                    $package->status = 'PENDING_PICKUP';
                }
            } else {
                // Direct Express
                $package->status = 'PENDING_PICKUP';
            }

            $package->save();

            return response()->json([
                'message' => 'Colis enregistré avec succès.',
                'package' => $package->load('collection_ride')
            ]);
        } catch (\Exception $e) {
            Log::error("Package store error: " . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la création : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get tracking timeline for a package.
     */
    public function track($code)
    {
        $package = PackageRequest::where('tracking_code', $code)
            ->with(['pickup_station', 'dropoff_station', 'company', 'provider', 'collection_ride'])
            ->firstOrFail();

        // Dynamic Label logic
        $pickupLabel = $package->needs_collection ? 'Collecte par livreur' : 'Dépôt en gare';
        $pathLabel = $package->type === 'INSTANT_DELIVERY' ? 'Livraison en cours' : 'Colis en cours de route (Car)';

        $timeline = [
            ['status' => 'CREATED', 'label' => 'Commande enregistrée', 'time' => $package->created_at, 'completed' => true],
            ['status' => 'PENDING_PICKUP', 'label' => $pickupLabel, 'time' => null, 'completed' => false],
            ['status' => 'DEPOSITED', 'label' => 'Colis réceptionné en gare', 'time' => null, 'completed' => false],
            ['status' => 'IN_TRANSIT', 'label' => $pathLabel, 'time' => null, 'completed' => false],
            ['status' => 'ARRIVED', 'label' => 'Arrivé à destination', 'time' => null, 'completed' => false],
            ['status' => 'DELIVERED', 'label' => 'Colis remis (OTP Validé)', 'time' => null, 'completed' => false],
        ];

        // Status Order mapping
        $statusOrder = ['CREATED', 'PENDING_PICKUP', 'DEPOSITED', 'IN_TRANSIT', 'ARRIVED', 'DELIVERED'];
        $currentIndex = array_search($package->status, $statusOrder);

        foreach ($timeline as $key => $item) {
            $itemOrder = array_search($item['status'], $statusOrder);
            if ($itemOrder <= $currentIndex) {
                $timeline[$key]['completed'] = true;
                if ($item['status'] == $package->status) {
                    $timeline[$key]['time'] = $package->updated_at;
                }
            }
        }

        return response()->json([
            'package' => $package,
            'timeline' => $timeline
        ]);
    }
}
