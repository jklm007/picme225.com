<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo "ProviderSelectedServices: " . json_encode(\Illuminate\Support\Facades\Schema::getColumnListing('provider_selected_services')) . "\n";
