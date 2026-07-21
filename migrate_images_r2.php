<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Service; // Categories
use App\Models\ServiceType; // Vehicles
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

echo "=== Démarrage de la migration des images vers Cloudflare R2 ===" . PHP_EOL;

function uploadToR2($localPath, $filename) {
    if (empty($localPath)) return null;

    // Check potential local paths in pod
    $searchPaths = [
        '/tmp/' . ltrim($localPath, '/'),
        '/app/public/' . ltrim($localPath, '/'),
        '/app/public/storage/' . ltrim($localPath, '/'),
        '/app/storage/app/public/' . ltrim($localPath, '/'),
        '/app/public/uploads/' . ltrim($localPath, '/'),
    ];

    $foundPath = null;
    foreach ($searchPaths as $path) {
        if (File::exists($path) && !File::isDirectory($path)) {
            $foundPath = $path;
            break;
        }
    }

    if (!$foundPath) {
        echo "  [AVERTISSEMENT] Fichier introuvable localement : $localPath" . PHP_EOL;
        return null;
    }

    $contents = File::get($foundPath);
    $extension = File::extension($foundPath);
    
    // Clean R2 destination path
    $r2Path = 'uploads/' . uniqid() . '.' . $extension;

    try {
        // Upload via driver s3 (R2)
        Storage::disk('s3')->put($r2Path, $contents);
        
        // Get URL
        $url = Storage::disk('s3')->url($r2Path);
        
        echo "  [OK] Uploadé : $localPath -> $url" . PHP_EOL;
        return $url;
    } catch (\Exception $e) {
        echo "  [ERREUR] Échec de l'upload pour $localPath : " . $e->getMessage() . PHP_EOL;
        return null;
    }
}

// 1. Migrer les catégories (Service model)
echo PHP_EOL . "--- MIGRATION DES CATEGORIES (Service) ---" . PHP_EOL;
$categories = Service::all();
foreach ($categories as $cat) {
    if (empty($cat->image)) {
        echo "Catégorie ID {$cat->id} ({$cat->name}) n'a pas d'image." . PHP_EOL;
        continue;
    }

    if (str_starts_with($cat->image, 'http')) {
        echo "Catégorie ID {$cat->id} ({$cat->name}) a déjà une URL distante." . PHP_EOL;
        continue;
    }

    echo "Migration image pour Catégorie ID {$cat->id} ({$cat->name}) : {$cat->image}" . PHP_EOL;
    $newUrl = uploadToR2($cat->image, $cat->name);
    if ($newUrl) {
        $cat->image = $newUrl;
        $cat->save();
        echo "  -> Base de données mise à jour !" . PHP_EOL;
    }
}

// 2. Migrer les types de service (ServiceType model)
echo PHP_EOL . "--- MIGRATION DES VEHICULES (ServiceType) ---" . PHP_EOL;
$serviceTypes = ServiceType::all();
foreach ($serviceTypes as $st) {
    if (empty($st->image)) {
        echo "Véhicule ID {$st->id} ({$st->name}) n'a pas d'image." . PHP_EOL;
        continue;
    }

    if (str_starts_with($st->image, 'http')) {
        echo "Véhicule ID {$st->id} ({$st->name}) a déjà une URL distante." . PHP_EOL;
        continue;
    }

    echo "Migration image pour Véhicule ID {$st->id} ({$st->name}) : {$st->image}" . PHP_EOL;
    $newUrl = uploadToR2($st->image, $st->name);
    if ($newUrl) {
        $st->image = $newUrl;
        $st->save();
        echo "  -> Base de données mise à jour !" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Migration Terminée ! ===" . PHP_EOL;
