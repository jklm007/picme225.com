<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$services = \App\ServiceType::get(['id', 'name', 'type', 'allowed_variants']);
foreach ($services as $s) {
    $variants = is_array($s->allowed_variants) ? json_encode($s->allowed_variants) : $s->allowed_variants;
    echo "ID: {$s->id}, Name: {$s->name}, Type: {$s->type}, Variants: {$variants}\n";
}
