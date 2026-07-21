<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$services = \App\ServiceType::with('company')->get();
$result = [];
foreach($services as $s) {
    if (strpos(strtolower($s->name), 'voyage') !== false || strpos(strtolower($s->name), 'express') !== false || strpos(strtolower($s->name), 'partag') !== false || $s->is_interregional == 1) {
        $result[] = [
            'id' => $s->id,
            'name' => $s->name,
            'company' => $s->company ? $s->company->name : 'Aucune (Standard)',
            'allowed_variants' => $s->allowed_variants
        ];
    }
}
echo json_encode($result, JSON_PRETTY_PRINT);
