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

class UserDeliveryController extends Controller
{
    /**
     * TODO Phase 2: Estimate fare for a delivery request.
     */
    public function estimated_fare_delivery(Request $request)
    {
        return response()->json(['status' => 'success', 'message' => 'Delivery fare estimation - Phase 2', 'data' => []]);
    }

    /**
     * TODO Phase 2: Create a new delivery request.
     */
    public function send_delivery_request(Request $request)
    {
        return response()->json(['status' => 'success', 'message' => 'Send delivery request - Phase 2', 'data' => null]);
    }

}