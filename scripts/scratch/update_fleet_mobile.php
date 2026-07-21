<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Mise à jour du compte Fleet Demo avec un numéro de téléphone pour permettre la connexion
DB::table('fleets')->where('email', 'demo@appoets.com')->update(['mobile' => '8800112233']);

echo "Le numéro de téléphone du Gestionnaire de Flotte (demo@appoets.com) a été défini sur: 8800112233\n";
