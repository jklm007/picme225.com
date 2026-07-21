<?php

require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\Provider;
use App\ProviderService;
use Core\ServiceType; // Si le namespace est différent, ajuster
use Illuminate\Support\Facades\Hash;

echo "--- CREATION DES COMPTES DE TEST ---\n";

// 1. Créer User
try {
    $user = User::updateOrCreate(
        ['email' => 'test@demo.com'],
        [
            'first_name' => 'Test',
            'last_name' => 'User',
            'mobile' => '+22501010101',
            'password' => bcrypt('123456'),
            'payment_mode' => 'CASH',
            'email_verified_at' => now(), // Si applicable
        ]
    );
    echo "✅ USER créé: test@demo.com / 123456\n";
} catch (\Exception $e) {
    echo "❌ Erreur User: " . $e->getMessage() . "\n";
}

// 2. Créer Provider
try {
    $provider = Provider::updateOrCreate(
        ['email' => 'driver@demo.com'],
        [
            'first_name' => 'Test',
            'last_name' => 'Driver',
            'mobile' => '+22502020202',
            'password' => bcrypt('123456'),
            'status' => 'approved', // IMPORTANT pour se connecter et passer online
            'wallet_balance' => 10000, // Pour accepter les commissions
            'eco_wallet_balance' => 5000,
            'commune' => 'Abidjan', // Champ obligatoire manquant
            'country_code' => '+225',
            'latitude' => 5.359952,
            'longitude' => -4.008256,
        ]
    );

    // Assigner un Service (ex: ID 1 = Sedan ou autre)
    // On vérifie si un service existe déjà
    $service = ProviderService::updateOrCreate(
        ['provider_id' => $provider->id],
        [
            'service_type_id' => 1,
            'status' => 'active',
            'service_number' => 'AB-123-CD',
            'service_model' => 'Toyota Test'
        ]
    );

    echo "✅ PROVIDER créé: driver@demo.com / 123456\n";
    echo "   -> Statut: approved\n";
    echo "   -> Service ID: 1 assigné\n";

} catch (\Exception $e) {
    echo "❌ Erreur Provider: " . $e->getMessage() . "\n";
}

echo "------------------------------------\n";
