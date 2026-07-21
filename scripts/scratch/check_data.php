<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "COMPANIES:\n";
$companies = App\InterurbanCompany::all();
foreach ($companies as $c) {
    echo "ID: " . $c->id . " | Name: " . $c->name . "\n";
}

echo "\nSERVICES:\n";
$services = App\ServiceType::all();
foreach ($services as $s) {
    echo "ID: " . $s->id . " | Name: " . $s->name . " | CompanyID: " . ($s->interurban_company_id ?? 'NULL') . "\n";
}
