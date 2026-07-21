<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$utb = DB::table('interurban_companies')->where('name', 'UTB')->first();
$sbta = DB::table('interurban_companies')->where('name', 'SBTA')->first();

if($utb) {
    App\Models\ServiceType::firstOrCreate(
        ['name' => 'UTB Express'],
        [
            'interurban_company_id' => $utb->id,
            'provider_name' => 'UTB',
            'type' => 'OUTSTATION',
            'calculator' => 'DISTANCE',
            'is_interregional' => 1,
            'allowed_variants' => ['partage'], // Voyage Partagé seulement
            'status' => 1,
            'price' => 0,
            'fixed' => 0,
            'distance' => 0,
            'minute' => 0
        ]
    );
}

if($sbta) {
    App\Models\ServiceType::firstOrCreate(
        ['name' => 'SBTA Express'],
        [
            'interurban_company_id' => $sbta->id,
            'provider_name' => 'SBTA',
            'type' => 'OUTSTATION',
            'calculator' => 'DISTANCE',
            'is_interregional' => 1,
            'allowed_variants' => ['partage'],
            'status' => 1,
            'price' => 0,
            'fixed' => 0,
            'distance' => 0,
            'minute' => 0
        ]
    );
}

echo "Services UTB et SBTA Express crees.\n";
