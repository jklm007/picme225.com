<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\WhatsappMessage::whereIn('id', [70, 71, 72, 73, 74, 75])->update([
    'batch_processed' => false,
    'status' => 'pending',
    'error_log' => null
]);
echo "Messages reset to pending.\n";
