<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\Notification;

class SendFirebasePushJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $token;
    protected $data;
    protected $notificationTitle;
    protected $notificationBody;

    /**
     * Create a new job instance.
     */
    public function __construct($token, $data, $notificationTitle = null, $notificationBody = null)
    {
        $this->token = $token;
        $this->data = $data;
        $this->notificationTitle = $notificationTitle;
        $this->notificationBody = $notificationBody;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('firebase_credentials.json'));
            
            $messaging = $firebase->createMessaging();

            $stringData = [];
            foreach ($this->data as $key => $value) {
                $stringData[$key] = (string) $value;
            }

            // CRUCIAL: For specific event types (like WEBRTC_CALL, NEW_CHAT_MESSAGE, INCOMING_RIDE, RESERVATION_REQUEST, MARKETPLACE_ORDER, MISSED_CALL),
            // we MUST send data-only pushes so that MyFirebaseMessagingService.onMessageReceived() runs in the background.
            // If the message has a notification object, the OS intercepts it and onMessageReceived is bypassed.
            $dataOnlyTypes = ['WEBRTC_CALL', 'NEW_CHAT_MESSAGE', 'INCOMING_RIDE', 'RESERVATION_REQUEST', 'MARKETPLACE_ORDER', 'MISSED_CALL'];
            if (isset($stringData['type']) && in_array($stringData['type'], $dataOnlyTypes)) {
                $this->notificationTitle = null;
                $this->notificationBody = null;
            }

            $message = CloudMessage::withTarget('token', $this->token)
                ->withData($stringData);

            // For data-only types, we must NOT include any notification block
            // (not even in AndroidConfig), otherwise the OS intercepts the message
            // and onMessageReceived() is NOT called when the app is in the background.
            $isDataOnly = isset($stringData['type']) && in_array($stringData['type'], $dataOnlyTypes);

            if ($isDataOnly) {
                $androidConfig = AndroidConfig::fromArray([
                    'priority' => 'high',
                    'ttl' => '60s',
                ]);
            } else {
                $androidConfig = AndroidConfig::fromArray([
                    'priority' => 'high',
                    'ttl' => '60s',
                    'notification' => [
                        'channel_id' => 'picme_alerts_v2',
                        'sound' => 'default'
                    ]
                ]);
            }
            $message = $message->withAndroidConfig($androidConfig);

            if ($this->notificationTitle && $this->notificationBody) {
                $notification = Notification::create($this->notificationTitle, $this->notificationBody);
                $message = $message->withNotification($notification);
            }

            $messaging->send($message);
            \Log::info("Async Push Sent to: " . $this->token);
        } catch (\Exception $e) {
            \Log::error("Async Push Error: " . $e->getMessage());
        }
    }
}
