<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('service_types', function (Blueprint $table) {
    if (!Schema::hasColumn('service_types', 'allowed_variants')) $table->json('allowed_variants')->nullable();
    if (!Schema::hasColumn('service_types', 'sharing_type')) $table->string('sharing_type')->nullable();
    if (!Schema::hasColumn('service_types', 'arret_discount_percent')) $table->decimal('arret_discount_percent', 5, 2)->default(0);
    if (!Schema::hasColumn('service_types', 'free_km_per_passenger')) $table->decimal('free_km_per_passenger', 8, 2)->default(0);
    if (!Schema::hasColumn('service_types', 'max_detour')) $table->decimal('max_detour', 8, 2)->nullable();
    if (!Schema::hasColumn('service_types', 'max_waiting_time')) $table->integer('max_waiting_time')->nullable();
    if (!Schema::hasColumn('service_types', 'price_per_km')) $table->decimal('price_per_km', 10, 2)->default(0);
    if (!Schema::hasColumn('service_types', 'price_per_segment')) $table->decimal('price_per_segment', 10, 2)->default(0);
    if (!Schema::hasColumn('service_types', 'outstation_price')) $table->decimal('outstation_price', 10, 2)->default(0);
    if (!Schema::hasColumn('service_types', 'feeder_trigger_radius')) $table->decimal('feeder_trigger_radius', 8, 2)->default(0);
    if (!Schema::hasColumn('service_types', 'rental_amount')) $table->decimal('rental_amount', 10, 2)->default(0);
    if (!Schema::hasColumn('service_types', 'day')) $table->decimal('day', 10, 2)->default(0);
    if (!Schema::hasColumn('service_types', 'max_distance')) $table->integer('max_distance')->nullable();
    if (!Schema::hasColumn('service_types', 'commune')) $table->string('commune')->nullable();
    if (!Schema::hasColumn('service_types', 'hour')) $table->string('hour')->nullable();
    if (!Schema::hasColumn('service_types', 'type')) $table->string('type')->nullable();
    if (!Schema::hasColumn('service_types', 'is_taxable')) $table->boolean('is_taxable')->default(0);
});

echo "Success: Missing columns added to service_types!\n";
