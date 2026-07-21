<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\ServiceType;
use Illuminate\Support\Facades\DB;

// Fix double JSON encoding using DB facade to avoid Eloquent casting it again
DB::table('service_types')
    ->where('id', 18)
    ->update(['allowed_variants' => json_encode(['prive', 'partage', 'arret_pdp'])]);

echo "Fix appliqué pour Woro-Woro (ID: 18) !\n";
