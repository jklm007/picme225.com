<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$data = \App\Service::with(['serviceTypes' => function($q) {
    $q->select('service_types.id', 'service_types.name', 'service_types.type');
}])->get(['id', 'name']);

echo json_encode($data, JSON_PRETTY_PRINT);
