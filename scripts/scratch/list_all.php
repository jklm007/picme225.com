<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

echo "--- INTERURBAN COMPANIES ---\n";
foreach (App\InterurbanCompany::all() as $c) {
    echo "ID: {$c->id} | Name: {$c->name}\n";
}

echo "\n--- SERVICE TYPES ---\n";
foreach (App\ServiceType::all() as $s) {
    echo "ID: {$s->id} | Name: {$s->name} | CompanyID: " . ($s->interurban_company_id ?? 'NULL') . "\n";
}
