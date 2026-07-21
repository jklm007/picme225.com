<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Setting::set('store_link_android', '');
\Setting::set('provider_store_link_android', '');
\Setting::save();

echo "Settings cleared.\n";
