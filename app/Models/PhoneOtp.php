<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour les codes OTP envoyés par SMS pour la vérification téléphonique.
 * Utilisé par ProviderAuth\TokenController pour l'inscription et la vérification.
 */
class PhoneOtp extends Model
{
    protected $table = 'phone_otps';

    protected $fillable = [
        'mobile',
        'otp',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
