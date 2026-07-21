<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\UserRequests;
use App\Models\OfflineBookingSms;
use App\Models\SmsOutbox;
use App\Jobs\ExpireOfflineBookingSms;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OfflineSmsDispatchService
{
    /**
     * Dispatch SMS requests to offline providers.
     *
     * @param array $providerIds
     * @param array|UserRequests $tripData
     * @return void
     */
    public function dispatchToOfflineProviders(array $providerIds, $tripData)
    {
        // Si $tripData est un tableau ou un ID, on charge le modèle UserRequests
        $request = null;
        if ($tripData instanceof UserRequests) {
            $request = $tripData;
        } else {
            $requestId = is_array($tripData) ? ($tripData['id'] ?? null) : $tripData;
            if ($requestId) {
                $request = UserRequests::with('user')->find($requestId);
            }
        }

        if (!$request) {
            Log::error("[OfflineSmsDispatchService] Course introuvable pour le dispatch SMS offline.", ['tripData' => $tripData]);
            return;
        }

        // Limiter le nombre de chauffeurs contactés par SMS en parallèle (ex: max 3)
        $maxAttempts = (int) env('OFFLINE_BOOKING_MAX_ATTEMPTS', 3);
        $providers = Provider::whereIn('id', array_slice($providerIds, 0, $maxAttempts))
            ->whereNotNull('mobile')
            ->get();

        Log::info("[OfflineSmsDispatchService] Début du dispatch SMS offline pour la course #{$request->id} vers " . $providers->count() . " chauffeurs.");

        foreach ($providers as $provider) {
            // Vérifier s'il n'y a pas déjà une demande SMS en cours pour ce couple course/chauffeur
            $exists = OfflineBookingSms::where('request_id', $request->id)
                ->where('provider_id', $provider->id)
                ->where('status', 'PENDING')
                ->exists();

            if ($exists) {
                continue;
            }

            // Générer un code SMS unique non-utilisé actuellement
            $smsCode = $this->generateUniqueSmsCode();

            // Durée de validité avant expiration (5 minutes)
            $timeoutSeconds = (int) env('OFFLINE_BOOKING_SMS_TIMEOUT', 300);
            $expiresAt = Carbon::now()->addSeconds($timeoutSeconds);

            // 1. Enregistrer dans offline_booking_sms
            $offlineBooking = OfflineBookingSms::create([
                'request_id' => $request->id,
                'provider_id' => $provider->id,
                'provider_phone' => $provider->mobile,
                'sms_code' => $smsCode,
                'status' => 'PENDING',
                'expires_at' => $expiresAt
            ]);

            // 2. Formater le message SMS
            $userName = $request->user ? ($request->user->first_name . ' ' . $request->user->last_name) : 'Client';
            $sAddr = $request->s_address ?: 'Départ inconnu';
            $dAddr = $request->d_address ?: 'Destination inconnue';
            $dist = $request->distance ? "({$request->distance} km)" : '';

            $message = "[PicME] Course disponible !\n";
            $message .= "Client : {$userName}\n";
            $message .= "Départ : {$sAddr}\n";
            $message .= "Arrivée : {$dAddr} {$dist}\n";
            $message .= "Répondez par SMS :\n";
            $message .= "OUI {$smsCode} (pour accepter)\n";
            $message .= "NON {$smsCode} (pour refuser)\n";
            $message .= "Expire dans " . ($timeoutSeconds / 60) . " min.";

            // Détecter le réseau pour optimiser l'envoi
            $network = $this->detectNetwork($provider->mobile);

            // 3. Ajouter à la file d'attente sms_outbox pour traitement par le robot
            SmsOutbox::create([
                'phone_number' => $provider->mobile,
                'message' => $message,
                'network' => $network,
                'status' => 'PENDING'
            ]);

            Log::info("[OfflineSmsDispatchService] SMS de demande programmé pour Chauffeur #{$provider->id} ({$provider->mobile}) avec le code : {$smsCode}");

            // 4. Programmer le job d'expiration du code SMS dans 5 minutes
            // Note: on n'exécute pas le job si la queue est en mode 'sync',
            // car le delay() est ignoré et le booking serait expiré immédiatement.
            $queueDriver = config('queue.default', 'sync');
            if (class_exists(ExpireOfflineBookingSms::class) && $queueDriver !== 'sync') {
                ExpireOfflineBookingSms::dispatch($offlineBooking->id)->delay($expiresAt);
            } else {
                Log::info("[OfflineSmsDispatchService] Job d'expiration non programmé (queue=sync). L'expiration sera gérée par expires_at.");
            }
        }
    }

    /**
     * Génère un code unique de 4 chiffres.
     */
    private function generateUniqueSmsCode(): string
    {
        do {
            $code = (string) rand(1000, 9999);
            $exists = OfflineBookingSms::where('sms_code', $code)
                ->where('status', 'PENDING')
                ->exists();
        } while ($exists);

        return $code;
    }

    /**
     * Tente de détecter le réseau Ivoirien par le préfixe.
     */
    private function detectNetwork(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Nettoyer l'indicateur pays
        if (str_starts_with($phone, '225')) {
            $phone = substr($phone, 3);
        }

        // Préfixes Côte d'Ivoire (10 chiffres)
        // Orange : 07, 08, 09, 47, 48, 49, 57, 58, 59, 77, 78, 79, 87, 88, 89
        // MTN : 05, 04, 44, 45, 46, 54, 55, 56, 74, 75, 76, 84, 85, 86
        // Moov : 01, 02, 03, 40, 41, 42, 43, 50, 51, 52, 53, 70, 71, 72, 73, 80, 81, 82, 83
        
        if (strlen($phone) >= 2) {
            $prefix = substr($phone, 0, 2);
            
            $orangePrefixes = ['07', '08', '09', '47', '48', '49', '57', '58', '59', '77', '78', '79', '87', '88', '89'];
            $mtnPrefixes    = ['05', '04', '44', '45', '46', '54', '55', '56', '74', '75', '76', '84', '85', '86'];
            
            if (in_array($prefix, $orangePrefixes)) {
                return 'ORANGE';
            }
            if (in_array($prefix, $mtnPrefixes)) {
                return 'MTN';
            }
            return 'MOOV';
        }

        return 'ORANGE'; // Valeur par défaut
    }
}
