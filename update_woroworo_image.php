<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\ServiceType;

// Mettre a jour Woroworo
$woroworo = ServiceType::where('name', 'Woroworo')->first();
if ($woroworo) {
    $woroworo->image = 'service/woro-woro.webp';
    $woroworo->save();
    echo "Image du Woroworo mise a jour !\n";
} else {
    echo "Service Woroworo introuvable.\n";
}
