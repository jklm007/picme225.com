<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SmsOutbox;
use App\Models\OfflineBookingSms;
use App\Models\UserRequests;
use App\Models\ProviderService;
use App\Models\Provider;
use App\Http\Controllers\SendPushNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SmsBookingController extends Controller
{
    /**
     * GET /api/gateway/sms/outbox
     * Polling endpoint for the Android physical gateway to fetch pending SMS.
     */
    public function getOutbox(Request $request)
    {
        $smsList = SmsOutbox::where('status', 'PENDING')
            ->when($request->has('network'), function ($query) use ($request) {
                return $query->where('network', strtoupper($request->network));
            })
            ->orderBy('id', 'asc')
            ->limit(10)
            ->get();

        // Mark as SENDING to prevent double sending during polling
        foreach ($smsList as $sms) {
            $sms->status = 'SENDING';
            $sms->attempts += 1;
            $sms->save();
        }

        return response()->json([
            'status' => 'success',
            'count' => $smsList->count(),
            'data' => $smsList
        ]);
    }

    /**
     * POST /api/gateway/sms/outbox/confirm
     * Webhook called by the Android robot to confirm that SMS have been sent.
     */
    public function confirmSent(Request $request)
    {
        $ids = $request->input('ids', []);
        $nodeId = $request->input('gateway_node_id', 'unknown');

        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No message IDs specified.'], 400);
        }

        SmsOutbox::whereIn('id', $ids)->update([
            'status' => 'SENT',
            'sent_at' => Carbon::now(),
            'gateway_node_id' => $nodeId
        ]);

        Log::info("[SmsBookingController] Confirmation d'envoi SMS reçue du robot {$nodeId} pour les IDs : " . implode(', ', $ids));

        return response()->json([
            'status' => 'success',
            'message' => 'Messages marked as sent.'
        ]);
    }

    /**
     * POST /api/gateway/sms/inbox
     * Webhook called by the Android robot when it receives an SMS (driver reply like "OUI 4521").
     */
    public function handleInbox(Request $request)
    {
        $sender = $request->input('sender');
        $message = $request->input('message');
        $gatewayNodeId = $request->input('gateway_node_id', 'unknown');

        if (empty($sender) || empty($message)) {
            return response()->json(['status' => 'error', 'message' => 'Sender and message are required.'], 400);
        }

        Log::info("[SmsBookingController] Message reçu du robot: {$sender} -> '{$message}' via Node {$gatewayNodeId}");

        // Parser le message : format attendu "OUI 1234" ou "NON 1234"
        $message = trim(strtoupper($message));
        if (!preg_match('/^(OUI|NON)\s+(\d{4})$/', $message, $matches)) {
            // Vérifier si c'est une commande client
            $clientParser = new \App\Services\ClientSmsParser();
            $clientCommand = $clientParser->parseCommand($message);
            
            if ($clientCommand) {
                return $this->handleClientBooking($sender, $clientCommand);
            }
            
            Log::warning("[SmsBookingController] Format de message SMS invalide : '{$message}'");
            return response()->json(['status' => 'ignored', 'message' => 'Format de message invalide.']);
        }

        $decision = $matches[1];
        $smsCode = $matches[2];

        // Trouver la demande de réservation SMS correspondante.
        // On accepte le statut 'PENDING' OU un booking dont expires_at est encore dans le futur
        // (protection contre l'expiration prématurée en mode queue=sync).
        $booking = OfflineBookingSms::where('sms_code', $smsCode)
            ->where(function ($q) {
                $q->where('status', 'PENDING')
                  ->orWhere(function ($q2) {
                      $q2->where('status', 'EXPIRED')
                         ->where('expires_at', '>', \Carbon\Carbon::now());
                  });
            })
            ->first();

        if (!$booking) {
            Log::warning("[SmsBookingController] Code SMS non trouvé ou déjà traité : {$smsCode}");
            return response()->json(['status' => 'ignored', 'message' => 'Code SMS inconnu ou expiré.']);
        }

        // Normaliser le statut en PENDING si le booking était EXPIRED prématurément (queue=sync)
        if ($booking->status === 'EXPIRED') {
            $booking->status = 'PENDING';
            $booking->save();
        }

        // Vérifier l'expéditeur
        if (!$this->matchPhoneNumber($sender, $booking->provider_phone)) {
            Log::warning("[SmsBookingController] Expéditeur non autorisé pour le code {$smsCode}. Reçu de {$sender}, attendu {$booking->provider_phone}");
            return response()->json(['status' => 'ignored', 'message' => 'Numéro de téléphone non correspondant.']);
        }

        // Charger la course
        $userRequest = UserRequests::with('user')->find($booking->request_id);

        if (!$userRequest) {
            $booking->status = 'EXPIRED';
            $booking->save();
            return response()->json(['status' => 'ignored', 'message' => 'Course introuvable.']);
        }

        if ($decision === 'OUI') {
            // Traitement de l'acceptation
            if ($userRequest->status !== 'SEARCHING') {
                // La course n'est plus en recherche (ex: déjà acceptée ou annulée)
                $booking->status = 'EXPIRED';
                $booking->save();

                // Notifier le chauffeur par SMS
                $replyMsg = "[PicME] Désolé, cette course n'est plus disponible ou a été annulée.";
                $this->enqueueOutboundSms($booking->provider_phone, $replyMsg);

                Log::info("[SmsBookingController] Chauffeur #{$booking->provider_id} a répondu OUI mais la course #{$userRequest->id} est en statut {$userRequest->status}.");
                return response()->json(['status' => 'missed', 'message' => 'Course déjà pourvue ou annulée.']);
            }

            // Assigner le chauffeur et passer au statut ACCEPTED
            $userRequest->provider_id = $booking->provider_id;
            $userRequest->current_provider_id = $booking->provider_id;
            $userRequest->status = 'ACCEPTED';
            $userRequest->assigned_at = Carbon::now();
            $userRequest->save();

            // Mettre à jour le statut du booking SMS
            $booking->status = 'ACCEPTED';
            $booking->save();

            // Annuler toutes les autres demandes SMS en attente pour cette même course
            OfflineBookingSms::where('request_id', $userRequest->id)
                ->where('id', '!=', $booking->id)
                ->where('status', 'PENDING')
                ->update(['status' => 'EXPIRED']);

            // Mettre à jour le statut du service du chauffeur à 'riding' (occupé)
            ProviderService::where('provider_id', $booking->provider_id)->update(['status' => 'riding']);

            // Supprimer tous les filtres de requête restants
            \App\Models\RequestFilter::where('request_id', $userRequest->id)->delete();

            // Envoyer une notification push à l'utilisateur
            try {
                (new SendPushNotification)->RideAccepted($userRequest);
            } catch (\Exception $e) {
                Log::error("[SmsBookingController] Erreur notification push utilisateur : " . $e->getMessage());
            }

            // Reward chauffeur
            $provider = Provider::find($booking->provider_id);
            if ($provider) {
                $provider->increment('priority', 2);
            }

            // Moteur d'apprentissage IA
            try {
                if (class_exists(\App\Services\DispatchEngine\DriverLearningService::class)) {
                    (new \App\Services\DispatchEngine\DriverLearningService())->recordAcceptance($booking->provider_id);
                }
            } catch (\Exception $e) {
                Log::error("[SmsBookingController] Erreur recordAcceptance : " . $e->getMessage());
            }

            // Envoyer un SMS de confirmation au chauffeur
            $userName = $userRequest->user ? ($userRequest->user->first_name . ' ' . $userRequest->user->last_name) : 'Client';
            $sAddr = $userRequest->s_address ?: 'Départ';
            $otpInfo = \Setting::get('ride_otp', 0) == 1 ? "Code OTP: {$userRequest->otp}." : '';
            $replyMsg = "[PicME] Course acceptée ! Client: {$userName}. Départ: {$sAddr}. {$otpInfo} Renseignez le code au client pour démarrer.";
            
            $this->enqueueOutboundSms($booking->provider_phone, $replyMsg);

            // --- Notifier le CLIENT par SMS si sa course a été commandée offline ---
            if ($userRequest->user) {
                $userPhone = $userRequest->user->mobile;
                if (!empty($userPhone)) {
                    $providerName   = $provider ? ($provider->first_name . ' ' . $provider->last_name) : 'Votre chauffeur';
                    $providerPhone  = $booking->provider_phone;
                    $dAddr          = $userRequest->d_address ?: 'Destination';
                    $clientSmsMsg   = "[PicME] Bonne nouvelle ! {$providerName} a accepté votre course vers {$dAddr}. Il arrive. Appelez-le : {$providerPhone}.";
                    $this->enqueueOutboundSms($userPhone, $clientSmsMsg);
                    Log::info("[SmsBookingController] SMS de confirmation envoyé au client ({$userPhone}) pour la course #{$userRequest->id}.");
                }
            }

            Log::info("[SmsBookingController] Course #{$userRequest->id} acceptée avec succès par le chauffeur offline #{$booking->provider_id} via SMS.");
            return response()->json(['status' => 'accepted', 'message' => 'Course assignée avec succès.']);

        } else {
            // Traitement du refus (NON)
            $booking->status = 'REJECTED';
            $booking->save();

            // Moteur d'apprentissage IA
            try {
                if (class_exists(\App\Services\DispatchEngine\DriverLearningService::class)) {
                    (new \App\Services\DispatchEngine\DriverLearningService())->recordRejection($booking->provider_id);
                }
            } catch (\Exception $e) {
                Log::error("[SmsBookingController] Erreur recordRejection : " . $e->getMessage());
            }

            // Pénalité légère pour refus
            $provider = Provider::find($booking->provider_id);
            if ($provider) {
                $provider->decrement('priority', 1);
            }

            Log::info("[SmsBookingController] Chauffeur #{$booking->provider_id} a décliné la course #{$userRequest->id} via SMS.");

            // Si tous les chauffeurs ont répondu ou expiré, annuler la course
            $hasOtherPending = OfflineBookingSms::where('request_id', $userRequest->id)
                ->where('status', 'PENDING')
                ->exists();

            if (!$hasOtherPending && $userRequest->status === 'SEARCHING') {
                Log::info("[SmsBookingController] Plus aucune demande SMS en attente pour la course #{$userRequest->id}. Annulation.");
                $userRequest->status = 'CANCELLED';
                $userRequest->save();

                \App\Models\RequestFilter::where('request_id', $userRequest->id)->delete();

                try {
                    (new SendPushNotification)->ProviderNotAvailable($userRequest->user_id);
                } catch (\Exception $e) {
                    Log::error("[SmsBookingController] Erreur notification ProviderNotAvailable : " . $e->getMessage());
                }

                // --- SMS d'annulation au CLIENT si course commandée offline ---
                if ($userRequest->user) {
                    $userPhone = $userRequest->user->mobile;
                    if (!empty($userPhone)) {
                        $svcName = $userRequest->d_address ?: 'votre course';
                        $cancelMsg = "[PicME] Désolé, aucun chauffeur n'est disponible pour {$svcName} pour le moment. Veuillez réessayer dans quelques minutes.";
                        $this->enqueueOutboundSms($userPhone, $cancelMsg);
                        Log::info("[SmsBookingController] SMS d'annulation envoyé au client ({$userPhone}) - course #{$userRequest->id}.");
                    }
                }
            }

            return response()->json(['status' => 'rejected', 'message' => 'Refus enregistré.']);
        }
    }

    /**
     * Gère une réservation envoyée par SMS par un client offline.
     */
    private function handleClientBooking($senderPhone, $commandData)
    {
        // Nettoyer le numéro
        $cleanPhone = preg_replace('/[^0-9]/', '', $senderPhone);

        // 1. Chercher l'utilisateur de manière extrêmement robuste
        $withoutPrefix = $cleanPhone;
        if (str_starts_with($cleanPhone, '225')) {
            $withoutPrefix = substr($cleanPhone, 3);
        }

        $user = \App\Models\User::whereIn('mobile', [
            $cleanPhone,
            '+' . $cleanPhone,
            $withoutPrefix,
            '+' . $withoutPrefix,
            '0' . $withoutPrefix,
        ])->first();
        
        if (!$user) {
            Log::warning("[SmsBookingController] Client inconnu: {$senderPhone}");
            $replyMsg = "[PicME] Bonjour! Votre numéro n'est pas reconnu. Téléchargez l'appli PicMe ou inscrivez-vous d'abord pour commander par SMS.";
            $this->enqueueOutboundSms($senderPhone, $replyMsg);
            return response()->json(['status' => 'ignored', 'message' => 'Utilisateur inconnu.']);
        }

        // 2. Chercher le type de service (par ex: "MOTO", "TAXI")
        $serviceName = $commandData['service'];
        $serviceType = \App\Models\ServiceType::where('name', 'LIKE', "%{$serviceName}%")->first();
        
        if (!$serviceType) {
            Log::warning("[SmsBookingController] Service inconnu: {$serviceName}");
            $replyMsg = "[PicME] Le service '{$serviceName}' n'est pas reconnu. Essayez avec un service valide (ex: MOTO, TAXI).";
            $this->enqueueOutboundSms($senderPhone, $replyMsg);
            return response()->json(['status' => 'ignored', 'message' => 'Service inconnu.']);
        }

        // 3. Créer la course
        // Coordonnées fictives par défaut (centre Abidjan) car le SMS n'a pas de GPS
        $defaultLat = 5.30966;
        $defaultLng = -4.01266;

        try {
            $userRequest = new UserRequests();
            $userRequest->booking_id = 'BK-' . mt_rand(1000, 9999);
            $userRequest->user_id = $user->id;
            $userRequest->service_type_id = $serviceType->id;
            $userRequest->payment_mode = 'CASH';
            $userRequest->status = 'SEARCHING';
            $userRequest->current_provider_id = 0;
            $userRequest->distance = 0;
            $userRequest->otp = mt_rand(1000, 9999);
            $userRequest->package_id = 0;
            $userRequest->use_wallet = 0;
            $userRequest->s_address = $commandData['origin'];
            $userRequest->d_address = $commandData['destination'];
            $userRequest->s_latitude = $defaultLat;
            $userRequest->s_longitude = $defaultLng;
            $userRequest->d_latitude = $defaultLat;
            $userRequest->d_longitude = $defaultLng;
            // Note pour le chauffeur :
            $userRequest->route_key = "⚠️ COMMANDE SMS OFFLINE ⚠️\nDépart : " . $commandData['origin'] . "\nArrivée : " . $commandData['destination'];
            $userRequest->save();

            // 4. Lancer le dispatch hybride
            // On récupère tous les chauffeurs dispos pour ce service (simplifié)
            $activeProviderIds = \App\Models\ProviderService::where('status', 'active')
                ->where('service_type_id', $serviceType->id)
                ->pluck('provider_id');
                
            $providers = \App\Models\Provider::where('status', 'approved')
                ->whereIn('id', $activeProviderIds)
                ->get();
            
            if ($providers->isEmpty()) {
                // S'il n'y a aucun chauffeur online, on dispatch directement en SMS offline aux chauffeurs offline !
                $offlineProviderIds = \App\Models\ProviderService::where('status', 'offline')
                    ->where('service_type_id', $serviceType->id)
                    ->pluck('provider_id');
                    
                $offlineProviders = \App\Models\Provider::where('status', 'approved')
                    ->whereIn('id', $offlineProviderIds)
                    ->get();
                
                if ($offlineProviders->isNotEmpty()) {
                    app(\App\Services\OfflineSmsDispatchService::class)->dispatchToOfflineProviders(
                        $offlineProviders->pluck('id')->toArray(),
                        $userRequest
                    );
                } else {
                    $replyMsg = "[PicME] Aucun {$serviceName} n'est disponible actuellement. Veuillez réessayer plus tard.";
                    $this->enqueueOutboundSms($senderPhone, $replyMsg);
                    $userRequest->status = 'CANCELLED';
                    $userRequest->save();
                    return response()->json(['status' => 'ignored', 'message' => 'Aucun chauffeur.']);
                }
            } else {
                // Insérer dans request_filters pour le dispatch in-app normal
                foreach ($providers as $provider) {
                    \App\Models\RequestFilter::create([
                        'request_id' => $userRequest->id,
                        'provider_id' => $provider->id
                    ]);
                }
                
                // On peut aussi déclencher la logique de broadcast websocket classique si besoin
                // Mais avec les request_filters, ils le verront sur l'app.
            }

            // 5. Confirmer au client
            $replyMsg = "[PicME] Votre demande de {$serviceName} (De: {$commandData['origin']} A: {$commandData['destination']}) a bien été enregistrée. Recherche en cours...";
            $this->enqueueOutboundSms($senderPhone, $replyMsg);
            
            Log::info("[SmsBookingController] Commande client par SMS créée avec succès: BK {$userRequest->booking_id} par l'utilisateur {$user->id}");
            return response()->json(['status' => 'success', 'message' => 'Commande client créée avec succès.']);
            
        } catch (\Exception $e) {
            Log::error("[SmsBookingController] Erreur lors de la création de la course SMS client: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Erreur interne.']);
        }
    }

    /**
     * Compare two phone numbers to see if they belong to the same driver.
     */
    private function matchPhoneNumber($num1, $num2)
    {
        $num1 = preg_replace('/[^0-9]/', '', $num1);
        $num2 = preg_replace('/[^0-9]/', '', $num2);

        // Remove 225 prefix if present
        if (substr($num1, 0, 3) === '225') {
            $num1 = substr($num1, 3);
        }
        if (substr($num2, 0, 3) === '225') {
            $num2 = substr($num2, 3);
        }

        // Compare the last 10 digits (Ivorian standard format)
        return substr($num1, -10) === substr($num2, -10);
    }

    /**
     * Enqueue a new message in the outbox queue.
     */
    private function enqueueOutboundSms($phone, $message)
    {
        // Simple prefix check to detect network
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($cleanPhone, 0, 3) === '225') {
            $cleanPhone = substr($cleanPhone, 3);
        }

        $network = 'ORANGE';
        if (strlen($cleanPhone) >= 2) {
            $prefix = substr($cleanPhone, 0, 2);
            $orangePrefixes = ['07', '08', '09', '47', '48', '49', '57', '58', '59', '77', '78', '79', '87', '88', '89'];
            $mtnPrefixes    = ['05', '04', '44', '45', '46', '54', '55', '56', '74', '75', '76', '84', '85', '86'];
            
            if (in_array($prefix, $orangePrefixes)) {
                $network = 'ORANGE';
            } elseif (in_array($prefix, $mtnPrefixes)) {
                $network = 'MTN';
            } else {
                $network = 'MOOV';
            }
        }

        SmsOutbox::create([
            'phone_number' => $phone,
            'message' => $message,
            'network' => $network,
            'status' => 'PENDING'
        ]);
    }
}
