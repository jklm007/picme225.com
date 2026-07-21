<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id',
        'provider_id',
        'title',
        'message',
        'type',
        'action_id',
        'action_type',
        'is_read'
    ];

    /**
     * Helper pour déclencher une notification persistante + optionnel Push.
     */
    public static function send($recipient, $title, $message, $type = 'GENERAL', $actionId = null, $actionType = null)
    {
        $data = [
            'title'       => $title,
            'message'     => $message,
            'type'        => $type,
            'action_id'   => $actionId,
            'action_type' => $actionType,
        ];

        // Détection du destinataire
        if ($recipient instanceof \App\Models\User) {
            $data['user_id'] = $recipient->id;
        } elseif ($recipient instanceof \App\Models\Provider) {
            $data['provider_id'] = $recipient->id;
        } else {
            // Si c'est un ID numérique direct, on suppose User (rétro-compatibilité)
            $data['user_id'] = $recipient;
        }

        $notification = self::create($data);

        return $notification;
    }
}
