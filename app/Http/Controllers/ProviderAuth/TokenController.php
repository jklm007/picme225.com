<?php

namespace App\Http\Controllers\ProviderAuth;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\PhoneOtp; // TODO: Ce modèle n'existe pas encore — créer app/Models/PhoneOtp.php
use App\Models\Provider;
use App\Models\ProviderDevice;
use App\Models\ProviderService;
use App\Models\ServiceType;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\RequestFilter;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ResetPasswordOTP;
use Exception;

class TokenController extends Controller
{
    /**
     * Retourne la liste des hôpitaux.
     */
    public function hospitals()
    {
        return response()->json([
            'hospitals' => Hospital::all()
        ]);
    }

    /**
     * Retourne la liste des types de service.
     */
    public function services()
    {
        return response()->json([
            'services' => ServiceType::all()
        ]);
    }

    /**
     * Gère l'inscription d'un nouveau provider.
     */
    public function register(Request $request)
    {
        if ($request->has('mobile')) {
            $request->merge(['mobile' => \App\Helpers\PhoneHelper::normalize($request->mobile)]);
        }

        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'device_type' => 'required|string|in:android,ios',
            'device_token' => 'required|string',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:providers,email',
            'mobile' => 'required|string|min:10|unique:providers,mobile',
            'password' => 'required|string|min:6|confirmed',
            'service_type_id' => 'required|integer|exists:service_types,id',
            'service_number' => 'required|string|max:255',
            'service_model' => 'required|string|max:255',
            'commune' => 'sometimes|nullable|string|max:255',
            'hospital_id' => 'sometimes|nullable|integer|exists:hospitals,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Vérification stricte : Si le service est Communal, la commune est OBLIGATOIRE
        $serviceType = ServiceType::find($request->service_type_id);
        if ($serviceType && $serviceType->is_communal && empty($request->commune)) {
            return response()->json(['message' => 'Veuillez sélectionner votre commune d\'affectation pour ce service (Woro-Woro/Tricycle).'], 422);
        }

        try {
            $providerData = $request->only('first_name', 'last_name', 'email', 'mobile', 'commune');
            $providerData['mobile'] = \App\Helpers\PhoneHelper::normalize($request->mobile);
            $providerData['password'] = Hash::make($request->password);
            $providerData['status'] = 'onboarding'; // Statut initial en attente de validation

            $provider = Provider::create($providerData);

            ProviderService::create([
                'provider_id' => $provider->id,
                'service_type_id' => $request->service_type_id,
                'status' => 'offline',
                'service_number' => $request->service_number,
                'service_model' => $request->service_model,
                'hospital_id' => $request->hospital_id,
            ]);

            ProviderDevice::create([
                'provider_id' => $provider->id,
                'udid' => $request->device_id,
                'token' => $request->device_token,
                'type' => $request->device_type,
            ]);

            return response()->json([
                'message' => 'Inscription réussie',
                'provider' => $provider,
                'currency' => \Setting::get('currency', 'CFA'),
                'eco_currency_name' => \Setting::get('eco_currency_name', 'ECO'),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'inscription du provider: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de l\'inscription.'], 500);
        }
    }

    /**
     * Génère et envoie un OTP. Ne renvoie JAMAIS l'OTP à l'application.
     */
    public function sendOtp(Request $request)
    {
        if ($request->has('mobile')) {
            $request->merge(['mobile' => \App\Helpers\PhoneHelper::normalize($request->mobile)]);
        }

        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $mobile = \App\Helpers\PhoneHelper::normalize($request->input('mobile'));

        try {
            $otpCode = rand(1000, 9999); // OTP à 4 chiffres
            $expiresAt = now()->addMinutes(10);

            PhoneOtp::updateOrCreate(
                ['mobile' => $mobile],
                ['otp' => $otpCode, 'expires_at' => $expiresAt]
            );

            // *** INTÉGREZ VOTRE LOGIQUE D'ENVOI DE SMS ICI ***
            // Exemple : SMSService::send($mobile, "Votre code de vérification est : " . $otpCode);
            Log::info("OTP pour {$mobile}: {$otpCode}"); // Pour le débogage

            return response()->json([
                'status' => true,
                'message' => 'Un code a été envoyé à votre numéro.'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur envoi OTP: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Erreur lors de l\'envoi de l\'OTP.'], 500);
        }
    }

    /**
     * Vérifie l'OTP fourni par l'utilisateur. C'est le nouvel endpoint sécurisé.
     */
    public function verifyOtp(Request $request)
    {
        if ($request->has('mobile')) {
            $request->merge(['mobile' => \App\Helpers\PhoneHelper::normalize($request->mobile)]);
        }

        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string',
            'otp' => 'required|string|min:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $mobile = \App\Helpers\PhoneHelper::normalize($request->mobile);
        $otpRecord = PhoneOtp::where('mobile', $mobile)->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'Code invalide.'], 400);
        }

        if (now()->isAfter($otpRecord->expires_at)) {
            $otpRecord->delete();
            return response()->json(['message' => 'Le code a expiré.'], 400);
        }

        if ($otpRecord->otp !== $request->otp) {
            return response()->json(['message' => 'Le code est incorrect.'], 400);
        }

        $otpRecord->delete();

        return response()->json(['status' => true, 'message' => 'Numéro vérifié avec succès.']);
    }

    /**
     * Authentification d'un provider existant.
     */
    public function authenticate(Request $request)
    {
        if ($request->has('mobile')) {
            $request->merge(['mobile' => \App\Helpers\PhoneHelper::normalize($request->mobile)]);
        }

        Log::info('Login attempt for mobile: ' . $request->mobile);
        Log::info('Login Request Data:', $request->all());

        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'password' => 'required',
            'device_id' => 'required',
            'device_type' => 'required|in:android,ios',
            'device_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $mobile = \App\Helpers\PhoneHelper::normalize($request->mobile);

        $provider = Provider::where('mobile', $mobile)
            ->first();

        if (!$provider || !Hash::check($request->password, $provider->password)) {
            return response()->json(['message' => 'Le numéro ou le mot de passe est incorrect.'], 401);
        }

        // $provider->tokens()->delete();
        $token = $provider->createToken('ProviderToken')->accessToken;

        ProviderDevice::updateOrCreate(
            ['provider_id' => $provider->id],
            ['udid' => $request->device_id, 'token' => $request->device_token, 'type' => $request->device_type]
        );

        $response = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'provider' => $provider,
            'currency' => \Setting::get('currency', 'CFA'),
            'eco_currency_name' => \Setting::get('eco_currency_name', 'ECO'),
        ];
        Log::info('Login response:', $response);
        return response()->json($response);
    }
    /**
     * Déconnexion du provider
     */
    public function logout(Request $request)
    {
        try {
            // Vérifier si l'utilisateur est bien authentifié
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            // Révoquer tous les tokens actifs de l'utilisateur
            $user->tokens()->delete();

            // Supprimer les données de l'appareil et mettre le statut offline
            ProviderDevice::where('provider_id', $user->id)->update(['udid' => '', 'token' => '']);
            ProviderService::where('provider_id', $user->id)->update(['status' => 'offline']);

            // Supprimer les requêtes en attente liées à ce provider
            $LogoutOpenRequest = RequestFilter::with(['request.provider', 'request'])
                ->where('provider_id', $user->id)
                ->whereHas('request', function ($query) use ($user) {
                    $query->where('status', 'SEARCHING');
                    $query->where('current_provider_id', '<>', $user->id);
                    $query->orWhereNull('current_provider_id');
                })->pluck('id');

            if ($LogoutOpenRequest->count() > 0) {
                RequestFilter::whereIn('id', $LogoutOpenRequest)->delete();
            }

            return response()->json(['message' => 'Déconnexion réussie'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur s\'est produite lors de la déconnexion'], 500);
        }
    }


    /**
     * Réinitialisation du mot de passe via OTP
     */
    public function forgot_password(Request $request)
    {
        if ($request->has('mobile')) {
            $request->merge(['mobile' => \App\Helpers\PhoneHelper::normalize($request->mobile)]);
        }

        // Validation du numéro de téléphone
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric', // Validation pour s'assurer que le numéro est valide
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            // Chercher le fournisseur par numéro de téléphone
            $mobile = \App\Helpers\PhoneHelper::normalize($request->mobile);
            $provider = Provider::where('mobile', $mobile)->first();

            // Vérifier si le fournisseur existe
            if (!$provider) {
                return response()->json(['error' => 'Aucun fournisseur trouvé avec ce numéro de téléphone'], 404);
            }

            // Générer un OTP
            $otp = mt_rand(100000, 999999);

            // Sauvegarder l'OTP
            $provider->otp = $otp;
            $provider->save();

            // Envoyer l'OTP par notification (assurez-vous que la classe ResetPasswordOTP existe et fonctionne)
            Notification::send($provider, new ResetPasswordOTP($otp));

            return response()->json(['message' => 'OTP envoyé à votre numéro de téléphone']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de l\'envoi de l\'OTP: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Changement du mot de passe
     */
    public function reset_password(Request $request)
    {
        if ($request->has('mobile')) {
            $request->merge(['mobile' => \App\Helpers\PhoneHelper::normalize($request->mobile)]);
        }

        // 1. CHANGER LA VALIDATION : On valide 'mobile' au lieu de 'id'
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|exists:providers,mobile', // Le mobile est requis et doit exister dans la table 'providers'
            'password' => 'required|string|min:6|confirmed', // 'confirmed' vérifie que 'password_confirmation' correspond
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            // 2. CHANGER LA RECHERCHE : On trouve le provider par 'mobile'
            $mobile = \App\Helpers\PhoneHelper::normalize($request->mobile);
            $provider = Provider::where('mobile', $mobile)->firstOrFail();

            // 3. MISE À JOUR DU MOT DE PASSE
            $provider->password = Hash::make($request->password);
            $provider->save();

            return response()->json(['message' => 'Mot de passe mis à jour avec succès.']);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Aucun utilisateur trouvé avec ce numéro de téléphone.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la mise à jour du mot de passe.'], 500);
        }
    }

    /**
     * Vérification de la disponibilité de l'email
     */
    public function verify(Request $request)
    {
        if ($request->has('mobile')) {
            $request->merge(['mobile' => \App\Helpers\PhoneHelper::normalize($request->mobile)]);
        }

        $validator = Validator::make($request->all(), [
            'mobile' => 'required|min:10|unique:providers',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        return response()->json(['message' => 'mobile  disponible']);
    }


    /**
     * Connexion/Inscription d'un Provider via Google API.
     * C'est cette méthode que votre app Android appelle.
     */
    /**
     * Connexion/Inscription d'un Provider via Google API.
     * C'est cette méthode que votre app Android appelle.
     */
    /**
     * Connexion/Inscription d'un Provider via Google API.
     * Cette version utilise une vérification manuelle du idToken via Guzzle.
     */
    // Dans app/Http/Controllers/ProviderAuth/TokenController.php

    public function googleViaAPI(Request $request)
    {
        if ($request->has('mobile')) {
            $request->merge(['mobile' => \App\Helpers\PhoneHelper::normalize($request->mobile)]);
        }

        $validator = Validator::make($request->all(), [
            'accessToken' => 'required', // Le idToken
            'device_type' => 'required|in:android,ios',
            'device_token' => 'required',
            'device_id' => 'required',
            'mobile' => 'sometimes|required|unique:providers,mobile', // 'sometimes' = optionnel mais requis si présent
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        try {
            // Valider le token avec Google
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://oauth2.googleapis.com/tokeninfo?id_token={$request->accessToken}");
            $googleUser = json_decode($response->getBody(), true);

            if (!isset($googleUser['email'])) {
                return response()->json(['status' => false, 'message' => "Token Google invalide."], 401);
            }

            // Rechercher un provider existant par son email
            $provider = Provider::where('email', $googleUser['email'])->first();

            if ($provider) {
                // L'UTILISATEUR EXISTE

                // Si son numéro est manquant ET que la requête actuelle n'en fournit pas un
                if (empty($provider->mobile) && !$request->has('mobile')) {
                    return response()->json([
                        'status' => false,
                        'error' => 'mobile_number_required',
                        'message' => 'Ce compte nécessite un numéro de téléphone.'
                    ], 422); // 422 Unprocessable Entity
                }

                // Si un nouveau numéro est fourni, on le met à jour.
                if ($request->has('mobile')) {
                    $provider->mobile = \App\Helpers\PhoneHelper::normalize($request->mobile);
                }

                // UTILISATEUR EXISTANT : Mise à jour des informations communes (ID social)
                $provider->social_unique_id = $googleUser['sub'];
                $provider->login_by = "google";
                $provider->save();

            } else {
                // NOUVEL UTILISATEUR

                // Si la requête ne contient pas les infos obligatoires du véhicule ou du mobile, on stoppe
                if (!$request->has('mobile') || empty($request->mobile)) {
                    return response()->json([
                        'status' => false,
                        'error' => 'mobile_number_required',
                        'message' => 'Un numéro de téléphone est requis pour les nouveaux comptes.'
                    ], 422);
                }
                
                // Pour créer le ProviderService, il FAUT ces informations
                if (!$request->has('service_type_id') || !$request->has('service_number') || !$request->has('service_model')) {
                    return response()->json([
                        'status' => false,
                        'error' => 'vehicle_info_required',
                        'message' => 'Les informations du véhicule (service_type_id, service_number, service_model) sont requises pour l\'inscription Google.'
                    ], 422);
                }

                $serviceType = ServiceType::find($request->service_type_id);
                if ($serviceType && $serviceType->is_communal && empty($request->commune)) {
                    return response()->json([
                        'status' => false,
                        'error' => 'commune_required',
                        'message' => 'Veuillez sélectionner votre commune d\'affectation pour ce service (Woro-Woro/Tricycle).'
                    ], 422);
                }

                $nameParts = explode(' ', $googleUser['name'] ?? 'Provider User', 2);
                $provider = new Provider();
                $provider->email = $googleUser['email'];
                $provider->first_name = $nameParts[0];
                $provider->last_name = $nameParts[1] ?? '';
                $provider->password = Hash::make(Str::random(16));
                $provider->picture = $googleUser['picture'] ?? null;
                $provider->mobile = \App\Helpers\PhoneHelper::normalize($request->mobile); // On assigne le numéro fourni
                $provider->commune = $request->commune; // Commune assignée si Woro
                $provider->status = 'banned'; // Statut par défaut / onboarding
                $provider->social_unique_id = $googleUser['sub'];
                $provider->login_by = "google";
                $provider->save();
                
                // CRÉATION DU SERVICE (Évite le crash de l'app Driver)
                ProviderService::create([
                    'provider_id' => $provider->id,
                    'service_type_id' => $request->service_type_id,
                    'status' => 'offline',
                    'service_number' => $request->service_number,
                    'service_model' => $request->service_model,
                ]);

            }

            // Mise à jour de l'appareil
            ProviderDevice::updateOrCreate(
                ['provider_id' => $provider->id],
                ['udid' => $request->device_id, 'token' => $request->device_token, 'type' => $request->device_type]
            );

            // Générer et renvoyer le token d'accès
            // $provider->tokens()->delete();
            $token = $provider->createToken('socialLogin')->accessToken;

            return response()->json([
                "status" => true,
                "token_type" => "Bearer",
                "access_token" => $token
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
