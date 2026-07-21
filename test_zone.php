<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$st = \App\ServiceType::where('name', 'like', '%Woro%')->get(['id', 'name', 'is_communal', 'is_intercommunal', 'zone_coverage'])->toArray();
var_dump($st);
