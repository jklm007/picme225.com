<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$publicDir = 'storage/app/public/service';

$types = App\ServiceType::all();
foreach ($types as $type) {
    if (strpos($type->image, '_right.png') !== false) {
        $oldPath = 'storage/app/public/' . $type->image;
        $newFile = str_replace('_right.png', '_v2.png', basename($type->image));
        $newPath = $publicDir . '/' . $newFile;
        
        if (file_exists($oldPath)) {
            rename($oldPath, $newPath);
            $type->image = 'service/' . $newFile;
            $type->save();
            echo "Renamed and updated $newFile\n";
        }
    }
}
