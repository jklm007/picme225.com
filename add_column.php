<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasColumn('service_types', 'shared_capacity')) {
    Schema::table('service_types', function (Blueprint $table) {
        $table->integer('shared_capacity')->default(1)->after('capacity');
    });
    echo "Column shared_capacity added!\n";
} else {
    echo "Column shared_capacity already exists.\n";
}
