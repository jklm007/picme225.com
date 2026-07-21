<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$serviceTypes = App\ServiceType::all();
foreach ($serviceTypes as $st) {
    if (strpos($st->image, 'trycloudflare.com') !== false) {
        $oldImage = $st->image;
        $urlParts = parse_url($st->image);
        $st->image = ltrim($urlParts['path'], '/');
        $st->save();
        echo "Updated $st->name: $oldImage -> $st->image\n";
    }
}
