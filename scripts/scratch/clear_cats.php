<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Désactiver les contraintes de clés étrangères pour le truncate
\Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
\App\Models\MarketplaceCategory::truncate();
\Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "Categories cleared.\n";
