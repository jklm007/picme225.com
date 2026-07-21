<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ProviderDevice;
use Exception;
use Log;
use Setting;

class SendPushNotification extends Controller
{
	/**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function RideAccepted($request, $userId = null){

    	$targetUser = $userId ?? $request->user_id;

    	return $this->sendPushToUser($targetUser, trans('api.push.request_accepted'));
    }

    /**
     * Driver Arrived at your location.
     *
     * @return void
     */
    public function user_schedule($user){

        return $this->sendPushToUser($user, trans('api.push.schedule_start'));
    }

    /**
     * New Incoming request
     *
     * @return void
     */
    public function provider_schedule($provider){

        return $this->sendPushToProvider($provider, trans('api.push.schedule_start'));

    }

    /**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function UserCancellRide($request){
        $providerId = (int) ($request->provider_id ?: $request->current_provider_id);
        if ($providerId <= 0) {
            return null;
        }
        return $this->sendPushToProvider($providerId, trans('api.push.user_cancelled'));
    }


    /**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function ProviderCancellRide($request){

        return $this->sendPushToUser($request->user_id, trans('api.push.provider_cancelled'));
    }

    /**
     * Driver Arrived at your location.
     *
     * @return void
     */
    public function Arrived($request){

        return $this->sendPushToUser($request->user_id, trans('api.push.arrived'));
    }

     /**
     * Driver Arrived at your location.
     *
     * @return void
     */
    public function Dropped($request){

        Log::info( trans('api.push.dropped').Setting::get('currency').$request->payment->total.' by '.$request->payment_mode);

        return $this->sendPushToUser($request->user_id, trans('api.push.dropped').Setting::get('currency').$request->payment->total.' by '.$request->payment_mode);
    }

    /**
     * Money added to user wallet.
     *
     * @return void
     */
    public function ProviderNotAvailable($user_id){

        return $this->sendPushToUser($user_id,trans('api.push.provider_not_available'));
    }

    /**
     * New Incoming request
     *
     * @return void
     */
    public function IncomingRequest($provider, $userRequest = null) {
        try {
            if (!$userRequest) {
                // Find the latest RequestFilter for this provider that is still SEARCHING
                $filter = \App\Models\RequestFilter::where('provider_id', $provider)
                    ->whereHas('request', function($query) {
                        $query->where('status', 'SEARCHING');
                    })
                    ->latest()
                    ->first();
                if ($filter) {
                    $userRequest = $filter->request;
                }
            }

            if ($userRequest) {
                $data = [
                    'type'            => 'INCOMING_RIDE',
                    'request_id'      => (string) $userRequest->id,
                    'pickup_address'  => $userRequest->s_address ?? '',
                    'dropoff_address' => $userRequest->d_address ?? '',
                    'message'         => trans('api.push.incoming_request'),
                    'title'           => "🚨 Nouvelle Course PicMe Pro !"
                ];
                
                return $this->sendPushToProvider($provider, $data, "🚨 Nouvelle Course PicMe Pro !");
            } else {
                return $this->sendPushToProvider($provider, trans('api.push.incoming_request'));
            }
        } catch (\Exception $e) {
            \Log::error("IncomingRequest Push Error: " . $e->getMessage());
            return $e;
        }
    }
    

    /**
     * Driver Documents verfied.
     *
     * @return void
     */
    public function DocumentsVerfied($provider_id){

        return $this->sendPushToProvider($provider_id, trans('api.push.document_verfied'));
    }


    /**
     * Money added to user wallet.
     *
     * @return void
     */
    public function WalletMoney($user_id, $money){

        return $this->sendPushToUser($user_id, $money.' '.trans('api.push.added_money_to_wallet'));
    }

    /**
     * Money charged from user wallet.
     *
     * @return void
     */
    public function ChargedWalletMoney($user_id, $money){
        return $this->sendPushToUser($user_id, $money.' '.trans('api.push.charged_from_wallet'));
    }

    /**
     * Community: Notify that a new shared trip is available in the corridor.
     */
    public function CommunityTripCreated($route_id, $message) {
        // Notifier les utilisateurs intéressés par ce corridor (ceux qui ont voté pour la route)
        $userIds = \App\Models\PdpRouteVote::where('pdp_route_id', $route_id)
            ->pluck('user_id')
            ->unique();
            
        foreach($userIds as $userId) {
            $this->sendPushToUser($userId, $message);
        }
        
        \Log::info("Community Push: Trajet sur route $route_id notifié à " . count($userIds) . " utilisateurs.");
    }

    /**
     * Community: Notify the driver when someone joins a trip.
     */
    public function CommunityTripJoined($provider_id, $message) {
        return $this->sendPushToProvider($provider_id, $message);
    }

    /**
     * Community: Notify when an intention becomes an active trip.
     */
    public function CommunityPledgeReached($user_id, $message) {
        return $this->sendPushToUser($user_id, $message);
    }

    /**
     * Marketplace: Notify the seller of a new order/sale.
     */
    public function MarketplaceOrderReceived($seller_id, $message) {
        $payload = [
            'type' => 'MARKETPLACE_ORDER',
            'alert' => $message
        ];
        return $this->sendPushToUser($seller_id, $payload);
    }

    /**
     * Marketplace: Notify the buyer that their ticket has been validated.
     */
    public function MarketplaceTicketValidated($user_id, $message) {
        return $this->sendPushToUser($user_id, $message);
    }

    /**
     * Marketplace: Notify the buyer about payment confirmation.
     */
    public function MarketplacePaymentConfirmed($user_id, $message) {
        return $this->sendPushToUser($user_id, $message);
    }

    /**
     * Rappel cloche planifiée : envoyé au chauffeur à 15, 5 et 1 minute avant la course.
     */
    public function ScheduledRideReminder($provider_id, $minutesLeft, $requestId, $pickupAddress, $scheduleAtIso)
    {
        $emoji = $minutesLeft >= 15 ? '🔔' : ($minutesLeft >= 5 ? '⚠️' : '🚨');
        $message = "SCHEDULED_REMINDER";

        try {
            $data = [
                'type'            => 'SCHEDULED_REMINDER',
                'minutes_left'    => (string) $minutesLeft,
                'request_id'      => (string) $requestId,
                'pickup_address'  => $pickupAddress ?? '',
                'schedule_at'     => $scheduleAtIso ?? '',
                'title'           => "{$emoji} Course planifiée dans {$minutesLeft} min",
                'message'         => $message,
            ];

            \Log::info("ScheduledRideReminder → provider #{$provider_id} [{$minutesLeft}min] ride #{$requestId}");

            return $this->sendPushToProvider($provider_id, $data, "{$emoji} Course planifiée dans {$minutesLeft} min");

        } catch (\Exception $e) {
            \Log::error("ScheduledRideReminder error: " . $e->getMessage());
            return $e;
        }
    }

    /**
     * Sending Push to a user Device.
     *
     * @return void
     */
    public function sendPushToUser($user_id, $push_message, $title = null){
    	try{
	    	$user = User::findOrFail($user_id);
            if($user->device_token != ""){
                \Log::info('sending push for user : '. $user->first_name);
                
                $notifTitle = $title;
                if ($notifTitle === null) {
                    $notifTitle = is_array($push_message) ? ($push_message['title'] ?? 'PicMe Pro') : 'PicMe Pro';
                }
                $notifBody = is_array($push_message) ? ($push_message['message'] ?? $push_message['alert'] ?? json_encode($push_message)) : $push_message;

                $data = [
                    'title'   => (string) $notifTitle,
                    'message' => (string) $notifBody,
                ];

                if (is_array($push_message)) {
                    foreach ($push_message as $k => $v) {
                        $data[$k] = (string) $v;
                    }
                }

                \App\Jobs\SendFirebasePushJob::dispatch(
                    $user->device_token,
                    $data,
                    $notifTitle,
                    $notifBody
                );
                return true;
            }
    	} catch(Exception $e){
    		\Log::error('sendPushToUser error: ' . $e->getMessage());
    		return $e;
    	}
    }

    /**
     * Sending Push to a provider Device.
     *
     * @return void
     */
    public function sendPushToProvider($provider_id, $push_message, $title = null){
    	try{
            if (empty($provider_id) || (int) $provider_id <= 0) {
                return null;
            }

	    	$provider = ProviderDevice::where('provider_id',$provider_id)->with('provider')->first();

            if (!$provider || empty($provider->token)) {
                return null;
            }

            if($provider->token != ""){
                \Log::info('sending push for provider : '. $provider->provider->first_name);

                $body = is_array($push_message) ? ($push_message['message'] ?? json_encode($push_message)) : $push_message;
                $notifTitle = $title;
                if ($notifTitle === null) {
                    $notifTitle = is_array($push_message) ? ($push_message['title'] ?? 'PicMe Pro') : 'PicMe Pro';
                }

                $data = [
                    'title'   => (string) $notifTitle,
                    'message' => (string) $body,
                ];

                if (is_array($push_message)) {
                    foreach ($push_message as $k => $v) {
                        $data[$k] = (string) $v;
                    }
                }

                // CRUCIAL: Pour le style 'WhatsApp' (Heads-up), la notification doit tre purement "Data"
                // Sinon, le systme Android intercepte le message en arrire-plan et empche le rveil de l'application.
                if (isset($data['type']) && $data['type'] === 'INCOMING_RIDE') {
                    $notifTitle = null;
                    $body = null;
                }

                \App\Jobs\SendFirebasePushJob::dispatch(
                    $provider->token,
                    $data,
                    $notifTitle,
                    $body
                );
                return true;
            }
    	} catch(Exception $e){
    		\Log::error('sendPushToProvider error: ' . $e->getMessage());
    		return $e;
    	}
    }

    /**
     * Broadcast a message to all registered users.
     */
    public function sendPushToAllUsers($push_message) {
        try {
            $users = User::where('device_token', '!=', '')->get();
            foreach ($users as $user) {
                $this->sendPushToUser($user->id, $push_message);
            }
            \Log::info("Broadcast Push: Message envoyé à " . count($users) . " utilisateurs.");
        } catch (Exception $e) {
            \Log::error("Broadcast Push Error: " . $e->getMessage());
        }
    }

    /**
     * Rappel du code Handshake au passager.
     */
    public function HandshakeReminder($user_id, $code){
        return $this->sendPushToUser($user_id, "N'oubliez pas de donner votre code Handshake au conducteur : " . $code);
    }

    /**
     * Envoyer un ordre de paiement automatique à l'application Gateway.
     */
    public function sendPayoutRequestToGateway($amount, $phone, $network, $withdrawalId) {
        $gatewayUser = User::where('email', 'gateway@picme.pro')->first();
        if ($gatewayUser && $gatewayUser->device_token != "") {
            $title = "ROBOT_PAYOUT_REQUEST";
            $data = [
                'type' => 'PAYOUT_REQUEST',
                'amount' => $amount,
                'phone' => $phone,
                'network' => $network,
                'id' => $withdrawalId
            ];
            
            return $this->sendPushToUser($gatewayUser->id, $data, $title);
        }
        return false;
    }

    /**
     * Delivery: Notify the user their delivery request has been created.
     */
    public function DeliveryRequestCreated($user_id, $tracking_code)
    {
        $payload = [
            'type'          => 'DELIVERY_CREATED',
            'tracking_code' => $tracking_code,
            'title'         => 'Livraison en cours de traitement',
            'message'       => "Votre demande de livraison #$tracking_code a été enregistrée. Un livreur sera bientôt attribué.",
        ];
        return $this->sendPushToUser($user_id, $payload, 'Livraison PicMe');
    }

    /**
     * Delivery: Notify the user a driver has been assigned to their package.
     */
    public function DeliveryDriverAssigned($user_id, $tracking_code, $driver_name)
    {
        $payload = [
            'type'          => 'DELIVERY_DRIVER_ASSIGNED',
            'tracking_code' => $tracking_code,
            'title'         => 'Livreur attribué !',
            'message'       => "$driver_name est en route pour récupérer votre colis ($tracking_code).",
        ];
        return $this->sendPushToUser($user_id, $payload, 'PicMe Livraison');
    }

    /**
     * Delivery: Notify the user their package has been delivered.
     */
    public function DeliveryCompleted($user_id, $tracking_code)
    {
        $payload = [
            'type'          => 'DELIVERY_COMPLETED',
            'tracking_code' => $tracking_code,
            'title'         => 'Colis livré !',
            'message'       => "Votre colis #$tracking_code a été remis au destinataire avec succès.",
        ];
        return $this->sendPushToUser($user_id, $payload, 'PicMe Livraison');
    }

    /**
     * Subscription: Notify a user their subscription has been activated.
     */
    public function SubscriptionActivated($user_id, $plan_name, $expires_date)
    {
        $payload = [
            'type'         => 'SUBSCRIPTION_ACTIVATED',
            'plan_name'    => $plan_name,
            'expires_date' => $expires_date,
            'title'        => 'Abonnement activé !',
            'message'      => "Votre abonnement $plan_name est actif jusqu'au $expires_date.",
        ];
        return $this->sendPushToUser($user_id, $payload, 'PicMe Abonnements');
    }

    /**
     * Subscription: Remind user their subscription expires soon.
     */
    public function SubscriptionExpiringSoon($user_id, $plan_name, $days_left)
    {
        $payload = [
            'type'      => 'SUBSCRIPTION_EXPIRING',
            'plan_name' => $plan_name,
            'days_left' => (string) $days_left,
            'title'     => 'Abonnement expirant bientôt',
            'message'   => "Votre abonnement $plan_name expire dans $days_left jour(s). Renouvelez maintenant !",
        ];
        return $this->sendPushToUser($user_id, $payload, 'PicMe Abonnements');
    }

    public function WaitlistJoined($user_id, $service_name, $position)
    {
        $payload = [
            'type' => 'WAITLIST_JOINED',
            'service_name' => $service_name,
            'position' => (string) $position,
            'title' => 'Liste d\'attente',
            'message' => "Vous êtes en position #$position sur la liste d'attente pour $service_name.",
        ];
        return $this->sendPushToUser($user_id, $payload, 'PicMe Waitlist');
    }

    public function WaitlistAvailable($user_id, $service_name)
    {
        $payload = [
            'type' => 'WAITLIST_AVAILABLE',
            'service_name' => $service_name,
            'title' => 'Service disponible !',
            'message' => "$service_name est maintenant disponible dans votre zone. Réservez maintenant !",
        ];
        return $this->sendPushToUser($user_id, $payload, 'PicMe Waitlist');
    }

}

