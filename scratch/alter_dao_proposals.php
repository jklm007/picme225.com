<?php
require 'bootstrap/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Running ALTER TABLE statement on dao_proposals...\n";
    DB::statement("ALTER TABLE dao_proposals MODIFY COLUMN type ENUM('PRICE_CHANGE', 'ROUTE_ADDITION', 'ROUTE_MODIFICATION', 'PARAMETER_CHANGE', 'STOP_ADDITION') NOT NULL");
    echo "✅ Success: Altered type column of dao_proposals successfully!\n";
} catch (\Exception $e) {
    echo "❌ Error: Failed to alter type column: " . $e->getMessage() . "\n";
}
