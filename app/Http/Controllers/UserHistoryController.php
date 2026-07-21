<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DB;
use Log;
use Auth;
use Hash;
use Storage;
use Setting;
use Exception;
use Notification;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Http\Controllers\SendPushNotification;
use App\Notifications\ResetPasswordOTP;
use App\Helpers\Helper;
use App\Card;
use App\User;
use App\Provider;
use App\Promocode;
use App\ServiceType;
use App\Service;
use App\KmHourServiceTypePrice;
use App\UserRequests;
use App\RequestFilter;
use App\PromocodeUsage;
use App\WalletPassbook;
use App\PromocodePassbook;
use App\ProviderService;
use App\UserRequestRating;
use App\Hospital;
use App\KmHour;
use App\ServiceTypeRental;
use App\PdpStop;
use App\PdpRoute;
use App\PdpRouteSegment;
use App\Services\SharedTripService;
use App\Http\Controllers\ProviderResources\TripController;
use Illuminate\Support\Facades\Validator;

class UserHistoryController extends Controller
{
    public function trips()
    {

        try {
            $UserRequests = UserRequests::UserTrips(Auth::user()->id)->get();
            if (!empty($UserRequests)) {
                foreach ($UserRequests as $key => $value) {
                    // OSM static map via staticmap.openstreetmap.de
                    $centerLat = ($value->s_latitude + $value->d_latitude) / 2;
                    $centerLng = ($value->s_longitude + $value->d_longitude) / 2;
                    $UserRequests[$key]->static_map = get_static_map($value->s_latitude, $value->s_longitude, $value->d_latitude, $value->d_longitude, $value->route_key);
                }
            }
            return $UserRequests;
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    public function trip_details(Request $request)
    {

        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id',
        ]);

        try {
            $UserRequests = UserRequests::UserTripDetails(Auth::user()->id, $request->request_id)->get();
            if (!empty($UserRequests)) {
                foreach ($UserRequests as $key => $value) {
                    $centerLat = ($value->s_latitude + $value->d_latitude) / 2;
                    $centerLng = ($value->s_longitude + $value->d_longitude) / 2;
                    $UserRequests[$key]->static_map = get_static_map($value->s_latitude, $value->s_longitude, $value->d_latitude, $value->d_longitude, $value->route_key);
                }
            }
            return $UserRequests;
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    public function upcoming_trips()
    {
        try {
            $UserRequests = UserRequests::UserUpcomingTrips(Auth::user()->id)->get();

            // Antigravity: Add Community Trip Bookings
            $communityBookings = \App\TripBooking::where('user_id', Auth::user()->id)
                ->where('status', 'CONFIRMED')
                ->with(['trip.user'])
                ->get();

            foreach ($communityBookings as $booking) {
                $item = new \stdClass();
                $item->id = $booking->id; 
                $item->booking_id = "PICKME-" . $booking->id;
                $item->s_address = $booking->trip->source_name;
                $item->d_address = $booking->trip->destination_name;
                $item->s_latitude = $booking->trip->source_lat;
                $item->s_longitude = $booking->trip->source_lng;
                $item->d_latitude = $booking->trip->destination_lat;
                $item->d_longitude = $booking->trip->destination_lng;
                $item->schedule_at = $booking->trip->departure_time;
                $item->is_community = true;
                $item->status = $booking->status;
                $item->payment_mode = $booking->payment_mode;
                
                $item->provider = [
                    'first_name' => $booking->trip->user->first_name,
                    'last_name' => $booking->trip->user->last_name,
                    'avatar' => $booking->trip->user->picture,
                    'rating' => "4.5",
                    'mobile' => $booking->trip->user->mobile
                ];
                
                $UserRequests->push($item);
            }

            if (!empty($UserRequests)) {
                foreach ($UserRequests as $key => $value) {
                    $centerLat = ($value->s_latitude + $value->d_latitude) / 2;
                    $centerLng = ($value->s_longitude + $value->d_longitude) / 2;
                    $UserRequests[$key]->static_map = get_static_map($value->s_latitude, $value->s_longitude, $value->d_latitude, $value->d_longitude, $value->route_key);
                }
            }
            return $UserRequests;
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    public function upcoming_trip_details(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer',
        ]);

        try {
            // First check if it's a community trip (we might need a flag in the request, but let's try to infer or check both)
            // If the mobile app sends is_community=true, we know.
            if ($request->has('is_community') && $request->is_community == "true") {
                $booking = \App\TripBooking::where('user_id', Auth::user()->id)
                    ->where('id', $request->request_id)
                    ->with(['trip.user'])
                    ->first();
                
                if($booking) {
                    $item = new \stdClass();
                    $item->id = $booking->id;
                    $item->booking_id = "PICKME-" . $booking->id;
                    $item->s_address = $booking->trip->source_name;
                    $item->d_address = $booking->trip->destination_name;
                    $item->s_latitude = $booking->trip->source_lat;
                    $item->s_longitude = $booking->trip->source_lng;
                    $item->d_latitude = $booking->trip->destination_lat;
                    $item->d_longitude = $booking->trip->destination_lng;
                    $item->schedule_at = $booking->trip->departure_time;
                    $item->is_community = true;
                    $item->status = $booking->status;
                    $item->payment_mode = $booking->payment_mode;
                    $item->provider = [
                        'first_name' => $booking->trip->user->first_name,
                        'last_name' => $booking->trip->user->last_name,
                        'avatar' => $booking->trip->user->picture,
                        'rating' => "4.5",
                        'mobile' => $booking->trip->user->mobile
                    ];
                    
                    $centerLat = ($item->s_latitude + $item->d_latitude) / 2;
                    $centerLng = ($item->s_longitude + $item->d_longitude) / 2;
                    $item->static_map = get_static_map($item->s_latitude, $item->s_longitude, $item->d_latitude, $item->d_longitude);

                    return [$item]; // Return as array for compatibility
                }
            }

            $UserRequests = UserRequests::UserUpcomingTripDetails(Auth::user()->id, $request->request_id)->get();
            if (!empty($UserRequests)) {
                foreach ($UserRequests as $key => $value) {
                    $centerLat = ($value->s_latitude + $value->d_latitude) / 2;
                    $centerLng = ($value->s_longitude + $value->d_longitude) / 2;
                    $UserRequests[$key]->static_map = get_static_map($value->s_latitude, $value->s_longitude, $value->d_latitude, $value->d_longitude, $value->route_key);
                }
            }
            return $UserRequests;
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

}