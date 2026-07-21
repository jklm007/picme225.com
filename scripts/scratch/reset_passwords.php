<?php

require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\Provider;
use Illuminate\Support\Facades\Hash;

echo "--- RESET DES MOTS DE PASSE ---\n";

// Reset User
$user = User::where('email', 'test@demo.com')->orWhere('mobile', '+22501010101')->first();
if ($user) {
    $user->password = Hash::make('123456');
    $user->mobile = '01010101'; // On simplifie pour le test
    $user->save();
    echo "✅ User (Passager) reset: mobile=01010101, pass=123456\n";
} else {
    echo "❌ User non trouvé\n";
}

// Reset Provider
$provider = Provider::where('email', 'driver@demo.com')->orWhere('mobile', '+22502020202')->first();
if ($provider) {
    $provider->password = Hash::make('123456');
    $provider->mobile = '02020202'; // On simplifie pour le test
    $provider->status = 'approved';
    $provider->save();
    echo "✅ Provider (Chauffeur) reset: mobile=02020202, pass=123456\n";
} else {
    echo "❌ Provider non trouvé\n";
}

echo "-------------------------------\n";
