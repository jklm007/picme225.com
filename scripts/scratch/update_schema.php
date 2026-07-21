<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasColumn('providers', 'opt_private_ride')) {
    Schema::table('providers', function (Blueprint $table) {
        $table->boolean('opt_private_ride')->default(0);
        $table->boolean('opt_share_ride')->default(0);
        $table->boolean('opt_multi_stop')->default(0);
        $table->boolean('opt_arret_ride')->default(0);
    });
    echo "Columns added successfully!\n";
} else {
    echo "Columns already exist.\n";
}
