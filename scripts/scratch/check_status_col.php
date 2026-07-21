<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = DB::select("SHOW COLUMNS FROM subscription_plans WHERE Field IN ('period', 'status', 'commission_type')");
foreach ($cols as $c) {
    echo "{$c->Field} → {$c->Type} | Default: {$c->Default}\n";
}
