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

class UserProfileController extends Controller
{
    public function details(Request $request)
    {

        $this->validate($request, [
            'device_type' => 'in:android,ios',
        ]);

        try {
            // PERFORMANCE: Use Auth::user() directly — avoids redundant User::find()
            $user = Auth::user();

            if ($user) {

                if ($request->has('device_token')) {
                    $user->device_token = $request->device_token;
                }

                if ($request->has('device_type')) {
                    $user->device_type = $request->device_type;
                }

                if ($request->has('device_id')) {
                    $user->device_id = $request->device_id;
                }

                if ($request->has('wallet_address')) {
                    $user->wallet_address = $request->wallet_address;
                }

                // NOTE: eco_token_balance est géré uniquement par transferToEco().
                // Ne pas auto-recalculer ici pour ne pas écraser les ECO achetés manuellement.

                if ($user->isDirty()) {
                    $user->save();
                }

                $user->currency = Setting::get('currency', 'CFA') ?: 'CFA';
                $user->sos = Setting::get('sos_number', '911');

                // PERFORMANCE: news:fetch is now handled only by schedule:work — no blocking call here.

                return response()->json($user);
            } else {
                return response()->json(['error' => trans('api.user.user_not_found')], 500);
            }
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }

    }

    public function update_profile(Request $request)
    {
        if ($request->has('mobile')) {
            $request->merge(['mobile' => \App\Helpers\PhoneHelper::normalize($request->mobile)]);
        }

        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'max:255',
            'display_name' => 'nullable|max:50|unique:users,display_name,' . Auth::user()->id,
            'email' => 'email|unique:users,email,' . Auth::user()->id,
            'mobile' => 'max:15|unique:users,mobile,' . Auth::user()->id,
            'picture' => 'nullable|mimes:jpeg,bmp,png',
        ]);

        try {

            $user = Auth::user();

            if ($request->has('first_name')) {
                $user->first_name = $request->first_name;
            }

            if ($request->has('last_name')) {
                $user->last_name = $request->last_name;
            }

            if ($request->has('display_name')) {
                $user->display_name = $request->display_name;
                // Sync with provider profile if exists
                \App\Provider::where('mobile', $user->mobile)->orWhere('email', $user->email)->update(['display_name' => $request->display_name]);
            }

            if ($request->has('email')) {
                $user->email = $request->email;
            }

            if ($request->has('mobile')) {
                $user->mobile = \App\Helpers\PhoneHelper::normalize($request->mobile);
            }

            if ($request->hasFile('picture')) {
                if ($user->picture) {
                    Storage::delete($user->picture);
                }
                $path = $request->picture->store('user/profile');
                $user->picture = $path;
                // Sync with provider avatar
                \App\Provider::where('mobile', $user->mobile)->orWhere('email', $user->email)->update(['avatar' => $path]);
            }

            if ($request->has('wallet_address')) {
                $user->wallet_address = $request->wallet_address;
            }

            $user->save();

            return response()->json($user);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.user.user_not_found')], 500);
        }

    }

    public function update_location(Request $request)
    {

        $this->validate($request, [
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => trans('api.user.user_not_found')], 500);
        }

        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;

        // PERFORMANCE: Écriture immédiate en Redis (non bloquant, TTL 10 min)
        Cache::put("user_location:{$user->id}", ['lat' => $lat, 'lng' => $lng], now()->addMinutes(10));

        // RATE-LIMIT: N'écrire en DB PostgreSQL qu'une fois toutes les 30 secondes max
        // Réduit les writes PostgreSQL de ~120/min à 2/min par utilisateur
        $dbWriteKey = "user_location_db_written:{$user->id}";
        if (!Cache::has($dbWriteKey)) {
            $user->latitude  = $lat;
            $user->longitude = $lng;
            $user->save();
            Cache::put($dbWriteKey, true, now()->addSeconds(30));
        }

        return response()->json(['message' => trans('api.user.location_updated')]);

    }

    public function updateKyc(Request $request)
    {
        $this->validate($request, [
            'kyc_document_type'  => 'required|in:ID_CARD,PASSPORT,LICENSE',
            'kyc_document_front' => 'required|mimes:jpeg,bmp,png|max:5120', // 5MB
            'kyc_document_back'  => 'nullable|mimes:jpeg,bmp,png|max:5120',
            'kyc_license_number' => 'required_if:kyc_document_type,LICENSE',
        ]);

        try {
            $user = User::findOrFail(Auth::user()->id);

            $user->kyc_document_type = $request->kyc_document_type;

            if ($request->hasFile('kyc_document_front')) {
                $user->kyc_document_front = Helper::upload_picture($request->kyc_document_front);
            }

            if ($request->hasFile('kyc_document_back')) {
                $user->kyc_document_back = Helper::upload_picture($request->kyc_document_back);
            }

            if ($request->has('kyc_license_number')) {
                $user->kyc_license_number = $request->kyc_license_number;
            }

            $user->kyc_status = 'PENDING';
            $user->save();

            return response()->json([
                'status'  => true,
                'message' => 'Documents KYC soumis avec succès. Votre compte est en cours de vérification.',
                'user'    => $user
            ]);

        } catch (Exception $e) {
            Log::error("Erreur KYC : " . $e->getMessage());
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

}