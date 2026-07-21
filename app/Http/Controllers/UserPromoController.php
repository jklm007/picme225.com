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

class UserPromoController extends Controller
{
    public function promocodes()
    {
        try {
            //$this->check_expiry();

            return PromocodeUsage::Active()
                ->where('user_id', Auth::user()->id)
                ->with('promocode')
                ->get();

        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function add_promocode(Request $request)
    {

        $this->validate($request, [
            'promocode' => 'required|exists:promocodes,promo_code',
        ]);

        try {

            $find_promo = Promocode::where('promo_code', $request->promocode)->first();

            if ($find_promo->status == 'EXPIRED' || (date("Y-m-d") > $find_promo->expiration)) {

                return response()->json([
                    'message' => trans('api.promocode_expired'),
                    'code' => 'promocode_expired'
                ]);

            } elseif (PromocodeUsage::where('promocode_id', $find_promo->id)->where('user_id', Auth::user()->id)->whereIN('status', ['ADDED', 'USED'])->count() > 0) {

                return response()->json([
                    'message' => trans('api.promocode_already_in_use'),
                    'code' => 'promocode_already_in_use'
                ]);

            } else {

                $promo = new PromocodeUsage;
                $promo->promocode_id = $find_promo->id;
                $promo->user_id = Auth::user()->id;
                $promo->status = 'ADDED';
                $promo->save();

                $count_id = PromocodePassbook::where('promocode_id', $find_promo->id)->count();
                //dd($count_id); 
                if ($count_id == 0) {

                    PromocodePassbook::create([
                        'user_id' => Auth::user()->id,
                        'status' => 'ADDED',
                        'promocode_id' => $find_promo->id
                    ]);
                }

                return response()->json([
                    'message' => trans('api.promocode_applied'),
                    'code' => 'promocode_applied'
                ]);
            }

        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }

    }

    public function help_details(Request $request)
    {

        try {

            return response()->json([
                'contact_number' => Setting::get('contact_number', ''),
                'contact_email' => Setting::get('contact_email', '')
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function wallet_passbook(Request $request)
    {
        try {

            $wallet_passbook = WalletPassbook::where('user_id', Auth::user()->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    $item->via = $item->via ?: 'CFA';
                    return $item;
                });
            return response()->json($wallet_passbook);

        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function promo_passbook(Request $request)
    {
        try {

            $promo_passbook = PromocodeUsage::where('user_id', Auth::user()->id)
                ->with('promocode')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    if (!$item->promocode) {
                        $item->promocode = (object)[
                            'promo_code' => 'N/A',
                            'discount' => 0,
                            'discount_type' => 'amount'
                        ];
                    }
                    return $item;
                });
            return response()->json($promo_passbook);

        } catch (Exception $e) {

            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

}