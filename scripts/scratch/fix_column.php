<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

try {
    if (!Schema::hasColumn('users', 'display_name')) {
        Schema::table('users', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('last_name');
        });
        echo "Column 'display_name' added successfully.\n";
    } else {
        echo "Column 'display_name' already exists.\n";
    }
} catch (\Exception $e) {
    echo "Error adding column: " . $e->getMessage() . "\n";
}
