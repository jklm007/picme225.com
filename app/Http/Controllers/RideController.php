<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Hospital;
use Auth;
use Setting;

class RideController extends Controller
{
    protected $UserAPI;

    public function __construct(UserApiController $UserAPI)
    {
        $this->middleware('auth');
        $this->UserAPI = $UserAPI;
    }

    /**
     * Confirmer une course.
     */
    public function confirm_ride(Request $request)
    {
        // Recherche des hôpitaux si des coordonnées sont fournies
        if ($request->filled(['latitude', 'longitude'])) {
            $distance = Setting::get('provider_search_radius', '10');
            $latitude = $request->latitude;
            $longitude = $request->longitude;

            $hospitals = Hospital::whereRaw("
                (1.609344 * 3956 * acos(
                    cos(radians(?)) * cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * sin(radians(latitude))
                )) <= ?
            ", [$latitude, $longitude, $latitude, $distance])->get();

            return $hospitals;
        }

        // Gestion des différents modes (rental, ambulance, outstation)
        if (empty($request->s_address) && empty($request->hos_address) && empty($request->o_trip_tab)) {
            $request->merge([
                'd_latitude' => $request->rental_lat,
                'd_longitude' => $request->rental_lng,
                's_latitude' => $request->rental_lat,
                's_longitude' => $request->rental_lng,
                'method' => 'rental',
                'd_address' => $request->rental_location,
                's_address' => $request->rental_location,
            ]);
        } elseif (empty($request->s_address) && empty($request->rental_location) && empty($request->o_trip_tab)) {
            $request->merge([
                'd_latitude' => $request->hospital_lat,
                'd_longitude' => $request->hospital_lng,
                's_latitude' => $request->amb_from_lat,
                's_longitude' => $request->amb_from_lng,
                'method' => 'ambulance',
                'd_address' => $request->hos_address,
                's_address' => $request->from_location,
            ]);
        } elseif (empty($request->s_address) && empty($request->rental_location) && empty($request->hos_address)) {
            $request->merge([
                'd_latitude' => $request->trip_d_lat,
                'd_longitude' => $request->trip_d_lng,
                's_latitude' => $request->trip_o_lat,
                's_longitude' => $request->trip_o_lng,
                'method' => 'outstation',
                'd_address' => $request->d_trip_tab,
                's_address' => $request->o_trip_tab,
                'round_trip' => $request->round_trip ?? 0,
            ]);
        }
          

        // Calcul des tarifs
        try {
            $fare_resp = $this->UserAPI->estimated_fare($request);
            if ($fare_resp->getStatusCode() != 200) {
                $data = json_decode($fare_resp->getContent());
                return redirect('/dashboard')->with('flash_error', $data->error ?? ($data->message ?? 'Erreur lors du calcul du tarif. Veuillez vérifier les adresses renseignées.'));
            }
            $fare = $fare_resp->getData();
        } catch (\Exception $e) {
            \Log::error('Confirm ride error: ' . $e->getMessage());
            return redirect('/dashboard')->with('flash_error', 'Impossible de calculer l\'itinéraire. Veuillez vérifier les adresses saisies.');
        }
       
        $service = (new Resource\ServiceResource)->show($request->service_type);
        $cards = (new Resource\CardResource)->index();

       
        // Mise à jour des coordonnées utilisateur si disponibles
        if ($request->filled(['current_latitude', 'current_longitude'])) {
            Auth::user()->update([
                'latitude' => $request->current_latitude,
                'longitude' => $request->current_longitude,
            ]);
        }

        return view('user.ride.confirm_ride', compact('request', 'fare', 'service', 'cards'));
    }

    /**
     * Créer une course.
     */
    public function create_ride(Request $request)
    {
        return $this->UserAPI->send_request($request);
    }

    /**
     * Obtenir le statut d'une course.
     */
    public function status()
    {
        return $this->UserAPI->request_status_check();
    }

    /**
     * Annuler une course.
     */
    public function cancel_ride(Request $request)
    {
        return $this->UserAPI->cancel_request($request);
    }

    /**
     * Noter une course.
     */
    public function rate(Request $request)
    {
        return $this->UserAPI->rate_provider($request);
    }
}
