<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Hospital;
use Auth;
use Setting;

class RideController extends Controller
{
    protected $UserAPI;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserApiController $UserAPI)
    {
        $this->middleware('auth');
        $this->UserAPI = $UserAPI;
    }


    /**
     * Ride Confirmation.
     *
     * @return \Illuminate\Http\Response
     */
    public function confirm_ride(Request $request)
    {   
        if(isset($_GET['latitude']) && $_GET['latitude'] !='' && isset($_GET['longitude']) && $_GET['longitude'] !='' ){
            $distance = Setting::get('provider_search_radius', '10');
                    $latitude = $_GET['latitude'];
                    $longitude = $_GET['longitude'];

            $hospitals = Hospital::whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                            ->get();
            return $hospitals;
        }
        if(empty($request->s_address) && empty($request->hos_address) && empty($request->o_trip_tab)){
            $request['d_latitude'] = $request->rental_lat;
            $request['d_longitude'] = $request->rental_lng;
            $request['s_latitude'] = $request->rental_lat;
            $request['s_longitude'] = $request->rental_lng;
            $request['method'] = "rental";
           // $request['rental_hours'] = $request->rental_hours;
            $request['d_address'] = $request->rental_location;
            $request['s_address'] = $request->rental_location;
        }else if(empty($request->s_address) && empty($request->rental_location) && empty($request->o_trip_tab)){
            $request['d_latitude'] = $request->hospital_lat;
            $request['d_longitude'] = $request->hospital_lng;
            $request['s_latitude'] = $request->amb_from_lat;
            $request['s_longitude'] = $request->amb_from_lng;
            $request['method'] = "ambulance";
           // $request['rental_hours'] = $request->rental_hours;
            $request['d_address'] = $request->hos_address;
            $request['s_address'] = $request->from_location;
        }else if(empty($request->s_address) && empty($request->rental_location) && empty($request->hos_address)){
            $request['d_latitude'] = $request->trip_d_lat;
            $request['d_longitude'] = $request->trip_d_lng;
            $request['s_latitude'] = $request->trip_o_lat;
            $request['s_longitude'] = $request->trip_o_lng;
            $request['method'] = "outstation";
           // $request['rental_hours'] = $request->rental_hours;
            $request['d_address'] = $request->d_trip_tab;
            $request['s_address'] = $request->o_trip_tab;
            if($request->has('round_trip')){
                $request['round_trip'] = $request->round_trip;
            }else{
                $request['round_trip'] = 0;
            }
        }
      
        $fare = $this->UserAPI->estimated_fare($request)->getData();
        $service = (new Resource\ServiceResource)->show($request->service_type);
        $cards = (new Resource\CardResource)->index();

        if($request->has('current_longitude') && $request->has('current_latitude'))
        {
            User::where('id',Auth::user()->id)->update([
                'latitude' => $request->current_latitude,
                'longitude' => $request->current_longitude
            ]);
        }

        return view('user.ride.confirm_ride',compact('request','fare','service','cards'));
    }

    /**
     * Create Ride.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_ride(Request $request)
    {
        return $this->UserAPI->send_request($request);
    }

    /**
     * Get Request Status Ride.
     *
     * @return \Illuminate\Http\Response
     */
    public function status()
    {
        return $this->UserAPI->request_status_check();
    }

    /**
     * Cancel Ride.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel_ride(Request $request)
    {
        return $this->UserAPI->cancel_request($request);
    }

    /**
     * Rate Ride.
     *
     * @return \Illuminate\Http\Response
     */
    public function rate(Request $request)
    {
        return $this->UserAPI->rate_provider($request);
    }
}
