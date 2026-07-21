<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$services = \DB::table('service_types')
    ->select('id', 'name', 'type', 'service_class', 'commission_percentage', 'status', 'is_communal')
    ->get();

echo "=== CATEGORIES DE SERVICES ===\n";
foreach ($services as $s) {
    $actif = $s->status ? 'ACTIF' : 'INACTIF';
    echo "[{$s->id}] {$s->name} | type:{$s->type} | class:{$s->service_class} | comm:{$s->commission_percentage}% | communal:" . ($s->is_communal ? 'Oui' : 'Non') . " | {$actif}\n";
}
