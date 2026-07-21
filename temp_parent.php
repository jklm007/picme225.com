<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$voyage = \App\Models\Service::where('name', 'Voyage')->first();
$s_types = \App\ServiceType::whereIn('name', ['UTB Express', 'SBTA Express'])->get();
foreach ($s_types as $st) {
    if ($voyage) {
        $exists = \Illuminate\Support\Facades\DB::table('service_service_type')
            ->where('service_id', $voyage->id)
            ->where('service_type_id', $st->id)
            ->exists();
        if (!$exists) {
            \Illuminate\Support\Facades\DB::table('service_service_type')->insert([
                'service_id' => $voyage->id,
                'service_type_id' => $st->id,
                'status' => 1,
                'name' => $st->name,
                'fixed' => 0,
                'price' => 0,
                'minute' => 0,
                'distance' => 0,
                'calculator' => 'MIN',
            ]);
            echo "Linked " . $st->name . " to Voyage!\n";
        }
    }
}
