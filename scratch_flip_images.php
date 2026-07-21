<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$brainDir = 'C:\Users\HP\.gemini\antigravity\brain\4669594d-0b06-45a7-be60-13532ded1036';
$publicDir = 'storage/app/public/service';

$updates = [
    '4x4' => ['glob' => '4x4_vehicle_right_*.png', 'file' => '4x4_right.png'],
    'Berline' => ['glob' => 'berline_vehicle_right_*.png', 'file' => 'berline_right.png'],
    'Pick-up' => ['glob' => 'pickup_vehicle_right_*.png', 'file' => 'pickup_right.png'],
    'Tricycle' => ['glob' => 'tricycle_vehicle_right_*.png', 'file' => 'tricycle_right.png'],
    'Car voyage' => ['glob' => 'car_vehicle_right_*.png', 'file' => 'car_right.png'],
    'Taxi inter-communal' => ['glob' => 'taxi_vehicle_right_*.png', 'file' => 'taxi_right.png'],
    'Taxi VTC' => ['glob' => 'vtc_vehicle_right_*.png', 'file' => 'vtc_right.png'],
    'Woroworo' => ['glob' => 'woro_vehicle_right_*.png', 'file' => 'woro_right.png'],
    'Moto' => ['glob' => 'moto_vehicle_right_*.png', 'file' => 'moto_right.png'],
    'Cargo' => ['glob' => 'cargo_vehicle_right_*.png', 'file' => 'cargo_right.png'],
    'Livraison Communal' => ['glob' => 'moto_vehicle_right_*.png', 'file' => 'moto_right.png'],
];

foreach ($updates as $name => $info) {
    $files = glob($brainDir . '/' . $info['glob']);
    if (count($files) > 0) {
        $src = $files[0];
        $dest = $publicDir . '/' . $info['file'];
        copy($src, $dest);
        App\ServiceType::where('name', $name)->update(['image' => 'service/' . $info['file']]);
        echo "Updated $name\n";
    }
}

// Handle the Van
$vanType = App\ServiceType::where('name', 'Van')->first();
if ($vanType) {
    // try to load the existing image
    $existingPath = 'storage/app/public/' . $vanType->image;
    if (file_exists($existingPath)) {
        if (strpos($existingPath, '.webp') !== false) {
            $im = @imagecreatefromwebp($existingPath);
        } else {
            $im = @imagecreatefrompng($existingPath);
        }
        
        if ($im) {
            imageflip($im, IMG_FLIP_HORIZONTAL);
            $dest = $publicDir . '/van_right.png';
            imagepng($im, $dest);
            imagedestroy($im);
            App\ServiceType::where('name', 'Van')->update(['image' => 'service/van_right.png']);
            echo "Flipped and updated Van\n";
        }
    }
}
