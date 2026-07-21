<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$brainDir = 'C:\Users\HP\.gemini\antigravity\brain\4669594d-0b06-45a7-be60-13532ded1036';
$publicDir = 'storage/app/public/service';

$updates = [
    '4x4' => ['glob' => '4x4_vehicle_right_*.png', 'file' => '4x4_v3.png'],
    'Berline' => ['glob' => 'berline_vehicle_right_*.png', 'file' => 'berline_v3.png'],
    'Pick-up' => ['glob' => 'pickup_vehicle_right_*.png', 'file' => 'pickup_v3.png'],
    'Tricycle' => ['glob' => 'tricycle_vehicle_right_*.png', 'file' => 'tricycle_v3.png'],
    'Car voyage' => ['glob' => 'car_vehicle_right_*.png', 'file' => 'car_v3.png'],
    'Taxi inter-communal' => ['glob' => 'taxi_vehicle_right_*.png', 'file' => 'taxi_v3.png'],
    'Taxi VTC' => ['glob' => 'vtc_vehicle_right_*.png', 'file' => 'vtc_v3.png'],
    'Woroworo' => ['glob' => 'woro_vehicle_right_*.png', 'file' => 'woro_v3.png'],
    'Moto' => ['glob' => 'moto_vehicle_right_*.png', 'file' => 'moto_v3.png'],
    'Cargo' => ['glob' => 'cargo_vehicle_right_*.png', 'file' => 'cargo_v3.png'],
    'Livraison Communal' => ['glob' => 'moto_vehicle_right_*.png', 'file' => 'moto_v3.png'],
];

foreach ($updates as $name => $info) {
    $files = glob($brainDir . '/' . $info['glob']);
    if (count($files) > 0) {
        $src = $files[0];
        $dest = $publicDir . '/' . $info['file'];
        // Correct ImageMagick command:
        // 1. read original
        // 2. fuzz and transparent white
        // 3. trim
        // 4. repage
        // 5. resize to 512x512 max (will keep aspect ratio)
        // 6. extent to 512x512 with transparent background
        $cmd = "magick \"$src\" -fuzz 15% -transparent white -trim +repage -resize 512x512 -background transparent -gravity center -extent 512x512 \"$dest\"";
        exec($cmd);
        
        App\ServiceType::where('name', $name)->update(['image' => 'service/' . $info['file']]);
        echo "Processed and updated $name\n";
    }
}

// Handle the Van
$vanType = App\ServiceType::where('name', 'Van')->first();
if ($vanType) {
    // try to load the existing old van image before all modifications (maybe ambulance.webp or van.webp)
    // we flipped it before. Let's just run ImageMagick on the current one and resize it.
    // Wait, the current one might be cut off. Do we have the original?
    // Let's assume van.webp is still there.
    $originalVan = 'storage/app/public/service/van.webp';
    if (file_exists($originalVan)) {
        // We flip it horizontally, then apply the same processing
        $dest = $publicDir . '/van_v3.png';
        $cmd = "magick \"$originalVan\" -flop -fuzz 15% -transparent white -trim +repage -resize 512x512 -background transparent -gravity center -extent 512x512 \"$dest\"";
        exec($cmd);
        App\ServiceType::where('name', 'Van')->update(['image' => 'service/van_v3.png']);
        echo "Flipped, processed and updated Van\n";
    } else {
        echo "Could not find original van image\n";
    }
}
