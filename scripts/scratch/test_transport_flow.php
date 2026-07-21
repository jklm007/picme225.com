<?php

use App\User;
use App\Trip;
use App\Intention;
use App\Services\TrajetMatchingService;
use App\Http\Controllers\SocialTransportController;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🚀 Démarrage des tests du flux de transport...\n";

// 1. Création d'un conducteur et d'un passager de test
$driver = User::firstOrCreate(['email' => 'driver_test@picme.com'], [
    'first_name' => 'Driver', 'last_name' => 'Test', 'password' => bcrypt('password'), 'wallet_balance' => 10000, 'mobile' => '0101010101'
]);
$passenger = User::firstOrCreate(['email' => 'passenger_test@picme.com'], [
    'first_name' => 'Passenger', 'last_name' => 'Test', 'password' => bcrypt('password'), 'wallet_balance' => 5000, 'mobile' => '0202020202'
]);

echo "✅ Utilisateurs prêts. Portefeuille passager: {$passenger->wallet_balance} FCFA\n";

// 2. Création d'un Trip (Offre)
$trip = Trip::create([
    'user_id' => $driver->id,
    'origin_name' => 'Angré', 'destination_name' => 'Plateau',
    'origin_lat' => 5.395, 'origin_lng' => -3.980,
    'destination_lat' => 5.320, 'destination_lng' => -4.020,
    'departure_time' => now()->addHour(),
    'seats_available' => 4, 'price' => 1000, 'status' => 'OPEN'
]);
echo "✅ Trip créé (ID: {$trip->id})\n";

// 3. Création d'une Intention (Demande)
$intention = Intention::create([
    'user_id' => $passenger->id,
    'origin_name' => 'Angré 7ème', 'destination_name' => 'Plateau',
    'origin_lat' => 5.398, 'origin_lng' => -3.982,
    'destination_lat' => 5.322, 'destination_lng' => -4.022,
    'earliest_departure' => now(), 'latest_departure' => now()->addHours(2),
    'seats_needed' => 1, 'status' => 'PENDING'
]);
echo "✅ Intention créée (ID: {$intention->id})\n";

// 4. Test Matching
$matchingService = new TrajetMatchingService();
$matches = $matchingService->findMatchesForIntention($intention);
echo "🔍 Matching: " . count($matches) . " trajet(s) trouvé(s).\n";

if (count($matches) > 0) {
    echo "⭐ Meilleur score: " . $matches[0]['score'] . "%\n";
    
    // 5. Test Booking
    echo "💳 Test de réservation...\n";
    Auth::login($passenger);
    $controller = new SocialTransportController();
    $request = new Request([
        'trip_id' => $trip->id,
        'seats' => 1
    ]);
    
    $response = $controller->bookTrip($request);
    $data = $response->getData();
    
    if (isset($data->success) && $data->success) {
        $passenger->refresh();
        echo "✅ Réservation réussie. Nouveau solde passager: {$passenger->wallet_balance} FCFA (Escrow OK)\n";
        
        // 6. Test Handshake
        $bookingId = $data->booking->id;
        $code = $data->booking->handshake_code;
        echo "🤝 Test Handshake avec code: $code\n";
        
        $hRequest = new Request([
            'booking_id' => $bookingId,
            'handshake_code' => $code
        ]);
        $hResponse = $controller->confirmHandshake($hRequest);
        
        if ($hResponse->getData()->success) {
            $driver->refresh();
            echo "🏁 Flux complet validé. Solde conducteur: {$driver->wallet_balance} FCFA (Paiement OK)\n";
        } else {
            echo "❌ Erreur Handshake\n";
        }
    } else {
        echo "❌ Erreur Booking: " . ($data->error ?? 'Inconnue') . "\n";
    }
} else {
    echo "❌ Aucun match trouvé. Vérifiez les coordonnées GPS.\n";
}

echo "🚀 Fin des tests.\n";
