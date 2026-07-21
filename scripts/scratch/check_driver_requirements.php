<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- PROVIDER SERVICES (ID: 1) ---\n";
$services = DB::table('provider_services')->where('provider_id', 1)->get();
foreach ($services as $s) {
    echo "ID: " . $s->id . " | TypeID: " . $s->service_type_id . " | Status: " . $s->status . " | Number: " . $s->service_number . "\n";
}

echo "\n--- PROVIDER DOCUMENTS (ID: 1) ---\n";
$docs = DB::table('provider_documents')->where('provider_id', 1)->get();
if ($docs->isEmpty()) {
    echo "NO DOCUMENTS FOUND FOR PROVIDER 1.\n";
} else {
    foreach ($docs as $d) {
        echo "DocID: " . $d->document_id . " | Status: " . $d->status . " | URL: " . $d->url . "\n";
    }
}
