<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;

$p = Provider::first();
echo "Sub Expires: " . $p->subscription_expires_at . "\n";
echo "Sub Level: " . $p->subscription_level . "\n";
echo "Sub Plan ID: " . $p->subscription_plan_id . "\n";
