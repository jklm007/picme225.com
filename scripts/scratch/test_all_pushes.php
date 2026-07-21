<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\ProviderDevice;
use App\Http\Controllers\SendPushNotification;

echo "--- DEBUT DES TESTS FIREBASE ---\n";

// 1. TEST COMMANDE CHAUFFEUR (Heads-up)
$providerDevice = ProviderDevice::whereNotNull('token')->where('token', '!=', '')->first();
if ($providerDevice) {
    echo "1. Envoi d'une fausse commande au chauffeur ID " . $providerDevice->provider_id . "...\n";
    $push = new SendPushNotification();
    $data = [
        'type' => 'INCOMING_RIDE',
        'request_id' => 9999,
        'pickup_address' => 'Aéroport FHB',
        'dropoff_address' => 'Plateau CCIA',
        'message' => 'Nouvelle Course Test'
    ];
    $push->sendPushToProvider($providerDevice->provider_id, $data, 'Nouvelle Course Test');
    echo "   -> Commande envoyée à Firebase.\n";
} else {
    echo "1. Aucun chauffeur avec Token trouvé.\n";
}

// 2. TEST MESSAGE MARKETPLACE
$user = User::whereNotNull('device_token')->where('device_token', '!=', '')->first();
if ($user) {
    echo "2. Envoi d'un message Marketplace à l'utilisateur ID " . $user->id . "...\n";
    $push = new SendPushNotification();
    $push->MarketplaceOrderReceived($user->id, "TEST: Vous avez une nouvelle commande sur la marketplace !");
    echo "   -> Message envoyé à Firebase.\n";
} else {
    echo "2. Aucun utilisateur avec Token trouvé.\n";
}

// 3. TEST RECHARGE WALLET
if ($user) {
    echo "3. Envoi d'une alerte de rechargement au même utilisateur...\n";
    $push = new SendPushNotification();
    $data = [
        'type' => 'WALLET_CREDITED',
        'amount' => 5000,
        'transaction_id' => 'TEST_WALLET_' . time(),
        'phone' => '0707070707',
        'message' => 'TEST: Votre compte a été rechargé de 5000 FCFA'
    ];
    $push->sendPushToUser($user->id, $data, 'Rechargement Validé');
    echo "   -> Alerte de recharge envoyée à Firebase.\n";
}

echo "--- FIN DES TESTS --- (Vérifiez les logs Laravel pour le succès final)\n";
