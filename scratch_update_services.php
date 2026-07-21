<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$updates = [
    '4x4' => 'service/4x4.png',
    'Berline' => 'service/berline.png',
    'Pick-up' => 'service/pickup.png',
    'Tricycle' => 'service/tricycle.png',
    'Car voyage' => 'service/car.png',
    'Taxi inter-communal' => 'service/taxi.png',
    'Taxi VTC' => 'service/vtc.png'
];

foreach ($updates as $name => $image) {
    App\ServiceType::where('name', $name)->update(['image' => $image]);
    echo "Updated $name to $image\n";
}
