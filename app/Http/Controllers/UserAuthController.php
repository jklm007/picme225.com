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

class UserAuthController extends Controller
{
    public function signin(Request $request)
    {
        if ($request->has('mobile')) {
            $request->merge(['mobile' => \App\Helpers\PhoneHelper::normalize($request->mobile)]);
        }

        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'password' => 'required|min:6',
            'device_id' => 'required',
            'device_type' => 'required|in:android,ios',
            'device_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $mobile = \App\Helpers\PhoneHelper::normalize($request->mobile);
        $user = User::where('mobile', $mobile)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Identifiants incorrects'], 401);
        }

        // Création du token Passport
        $token = $user->createToken('UserToken')->accessToken;

        // Mise à jour du device directement sur l'utilisateur
        $user->update([
            'device_id' => $request->device_id,
            'device_token' => $request->device_token,
            'device_type' => $request->device_type,
        ]);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user // Renvoie les informations de l'utilisateur
        ], Response::HTTP_OK);
    }

    public function signup(Request $request)
    {
        if ($request->has('mobile')) {
            $request->merge(['mobile' => \App\Helpers\PhoneHelper::normalize($request->input('mobile'))]);
        }
        $mobile = $request->input('mobile');
        $ghostUser = User::where('mobile', $mobile)->where('first_name', 'GHOST_AGENT')->first();
        
        $mobileRule = 'required|string|unique:users,mobile';
        if ($ghostUser) {
            $mobileRule = 'required|string'; // On ignore la règle d'unicité car c'est un compte fantôme à convertir
        }

        $validator = Validator::make($request->all(), [
            'device_type' => 'required|in:android,ios',
            'device_token' => 'required',
            'device_id' => 'required',
            'login_by' => 'required|in:manual',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => $mobileRule,
            'email' => 'present|nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'gender' => 'required|in:male,female',
            'picture' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Étape 2: Préparer les données pour la création
            $userData = $request->only([
                'first_name',
                'last_name',
                'mobile',
                'gender',
                'device_type',
                'device_token',
                'device_id',
                'login_by',
                'email' // On inclut l'email pour qu'il soit sauvegardé s'il est présent
            ]);

            // Si l'email n'est pas fourni dans la requête, s'assurer qu'il est bien `null`
            if (!$request->has('email')) {
                $userData['email'] = null;
            }

            $userData['password'] = Hash::make($request->password);
            $userData['payment_mode'] = 'CASH';
            $userData['mobile_verified_at'] = Carbon::now();

            // Étape 3: Créer l'utilisateur ou mettre à jour le compte fantôme
            if ($ghostUser) {
                $ghostUser->update($userData);
                $user = $ghostUser;
            } else {
                $user = User::create($userData);
            }

            // AMÉLIORATION : Générer le token d'accès directement
            $token = $user->createToken('Android App')->accessToken;

            return response()->json([
                'token_type' => 'Bearer',
                'access_token' => $token,
                'user' => $user
            ]);

        } catch (Exception $e) {
            Log::error('Erreur d\'inscription: ' . $e->getMessage());
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function checkMobileExists(Request $request)
    {
        if ($request->has('mobile')) {
            $request->merge(['mobile' => \App\Helpers\PhoneHelper::normalize($request->mobile)]);
        }

        // Valide que le champ 'mobile' est présent
        $this->validate($request, [
            'mobile' => 'required',
        ]);

        try {
            // Recherche un utilisateur avec ce numéro de mobile
            $mobile = \App\Helpers\PhoneHelper::normalize($request->mobile);
            $user = User::where('mobile', $mobile)->first();

            if ($user) {
                // Si l'utilisateur est trouvé, on renvoie son ID.
                // L'application Android utilisera cet ID pour réinitialiser le mot de passe.
                return response()->json([
                    'message' => 'User found.',
                    'user_id' => $user->id
                ]);
            } else {
                // Si l'utilisateur n'est pas trouvé, on renvoie une erreur 404 (Not Found).
                // L'application Android affichera un message d'erreur.
                return response()->json(['error' => 'User not found with this mobile number.'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong.'], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            User::where('id', $request->id)->update(['device_id' => '', 'device_token' => '']);
            return response()->json(['message' => trans('api.logout_success')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function change_password(Request $request)
    {

        $this->validate($request, [
            'password' => 'required|confirmed|min:6',
            'old_password' => 'required',
        ]);

        $User = Auth::user();

        if (Hash::check($request->old_password, $User->password)) {
            $User->password = bcrypt($request->password);
            $User->save();

            return response()->json(['message' => trans('api.user.password_updated')]);

        } else {
            return response()->json(['error' => trans('api.user.change_password')], 500);
        }

    }

    public function forgot_password(Request $request)
    {

        $this->validate($request, [
            'email' => 'required|email|exists:users,email',
        ]);

        try {

            $user = User::where('email', $request->email)->first();

            $otp = mt_rand(100000, 999999);

            $user->otp = $otp;
            $user->save();

            Notification::send($user, new ResetPasswordOTP($otp));

            return response()->json([
                'message' => 'OTP sent to your email!',
                'user' => $user
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function reset_password(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric|exists:users,id',
            'password' => 'required|confirmed|min:6',
        ]);

        try {
            $user = User::find($request->id);
            if (!$user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            // 1. Mettre à jour le mot de passe
            $user->password = bcrypt($request->password);
            $user->save();

            // 2. Créer des tokens pour connecter l'utilisateur automatiquement
            // IMPORTANT : La ligne suivante dépend de votre système d'authentification (Passport ou JWT)
            // Ceci est un exemple avec Passport/JWT.
            $token = $user->createToken('Android App')->accessToken;

            // 3. Renvoyer un message de succès ET les tokens
            return response()->json([
                'message' => 'Password Updated Successfully',
                'token_type' => 'Bearer',
                'access_token' => $token,
                // Si vous utilisez des refresh tokens, générez-en un ici également
                // 'refresh_token' => $refreshToken, 
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        // LA NOUVELLE LOGIQUE SIMPLIFIÉE :
        // Si l'utilisateur n'existe pas OU si son champ 'mobile' est vide,
        // alors la vérification du téléphone est requise.
        if (!$user || empty($user->mobile)) {
            return response()->json(['status' => 'phone_verification_required']);
        }

        // Si l'utilisateur existe ET a un numéro (peu importe si "vérifié"), on autorise la connexion directe.
        return response()->json(['status' => 'user_verified']);
    }

}