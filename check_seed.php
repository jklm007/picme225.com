<?php
chdir('/app');
require '/app/vendor/autoload.php';
$app = require_once '/app/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DATABASE SEED CHECK ===\n";
try {
    $settings = \DB::table('settings')->get();
    echo "Settings rows: " . $settings->count() . "\n";
    foreach ($settings as $s) {
        echo " - key: {$s->key}, val: " . substr($s->value, 0, 50) . "\n";
    }
} catch (\Exception $e) {
    echo "Settings DB Error: " . $e->getMessage() . "\n";
}

try {
    $admins = \DB::table('admins')->get();
    echo "Admins rows: " . $admins->count() . "\n";
    foreach ($admins as $a) {
        echo " - email: {$a->email}\n";
    }
} catch (\Exception $e) {
    echo "Admins DB Error: " . $e->getMessage() . "\n";
}

try {
    $services = \DB::table('service_types')->get();
    echo "ServiceTypes rows: " . $services->count() . "\n";
    foreach ($services as $s) {
        echo " - name: {$s->name}\n";
    }
} catch (\Exception $e) {
    echo "ServiceTypes DB Error: " . $e->getMessage() . "\n";
}
