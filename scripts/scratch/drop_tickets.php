<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

Schema::dropIfExists('driver_assignment_logs');
Schema::dropIfExists('ticket_validation_logs');
Schema::dropIfExists('tickets');

echo "Tables supprimées avec succès.\n";
