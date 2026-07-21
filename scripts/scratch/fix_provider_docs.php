<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- GLOBAL DOCUMENTS LIST ---\n";
$docs = DB::table('documents')->get();
foreach ($docs as $doc) {
    echo "ID: " . $doc->id . " | Name: " . $doc->name . " | Type: " . $doc->type . "\n";
}

echo "\n--- FIXING PROVIDER 1 (INSERTING DUMMY DOCS) ---\n";
foreach ($docs as $doc) {
    DB::table('provider_documents')->insert([
        'provider_id' => 1,
        'document_id' => $doc->id,
        'url' => 'http://lorempixel.com/512/512/business/Tranxit',
        'unique_id' => 'DEMO-'.rand(1000,9999),
        'status' => 'ACTIVE',
        'expires_at' => Carbon\Carbon::now()->addYear(),
        'created_at' => Carbon\Carbon::now(),
        'updated_at' => Carbon\Carbon::now(),
    ]);
}

echo "Dummy documents inserted for Provider 1.\n";
