<?php

use App\User;
use App\Provider;
use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Génération des Cartes PickMe (QR Tokens) pour les utilisateurs...\n";

User::whereNull('qr_id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        $user->update([
            'qr_id' => 'PM-U' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
            'qr_token' => Str::random(40)
        ]);
        echo "Card générée pour l'utilisateur: {$user->first_name}\n";
    }
});

echo "Génération des Cartes PickMe (QR Tokens) pour les conducteurs...\n";

Provider::whereNull('qr_id')->chunk(100, function ($providers) {
    foreach ($providers as $provider) {
        $provider->update([
            'qr_id' => 'PM-P' . str_pad($provider->id, 6, '0', STR_PAD_LEFT),
            'qr_token' => Str::random(40)
        ]);
        echo "Card générée pour le conducteur: {$provider->first_name}\n";
    }
});

echo "Opération terminée avec succès !\n";
