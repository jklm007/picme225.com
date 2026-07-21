<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('users', function (Blueprint $table) {
    if (Schema::hasColumn('users', 'subscription_plan_id')) {
        $table->dropColumn('subscription_plan_id');
    }
    if (Schema::hasColumn('users', 'subscription_expires_at')) {
        $table->dropColumn('subscription_expires_at');
    }
});

Schema::table('service_types', function (Blueprint $table) {
    if (Schema::hasColumn('service_types', 'requires_pro_subscription')) {
        $table->dropColumn('requires_pro_subscription');
    }
});

echo "Nettoyage terminé.\n";
