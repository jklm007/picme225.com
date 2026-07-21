<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$pivot = DB::table('service_service_type')->get();
echo "--- service_service_type rows: " . $pivot->count() . " ---\n";
foreach ($pivot as $row) {
    $st = DB::table('service_types')->where('id', $row->service_type_id)->first();
    $s = DB::table('services')->where('id', $row->service_id)->first();
    $stName = $st ? $st->name : 'Unknown';
    $sName = $s ? $s->name : 'Unknown';
    echo "Service: [{$sName} (ID: {$row->service_id})] | ServiceType: [{$stName} (ID: {$row->service_type_id})] | Status: {$row->status}\n";
}
