<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Supprimer TOUTES les catégories existantes et re-seed
echo "Suppression de toutes les catégories...\n";
\DB::table('marketplace_categories')->truncate();
echo "OK\n";

// Re-run le seeder
echo "Re-création des catégories (sans accents)...\n";
$seeder = new \Database\Seeders\MarketplaceCategorySeeder();
$seeder->run();
echo "OK\n";

// Vérifier les résultats
echo "\n=== CATEGORIES (noms) ===\n";
$cats = \App\Models\MarketplaceCategory::all(['id','name','parent_id']);
foreach ($cats as $c) {
    echo "  [{$c->id}] {$c->name}" . ($c->parent_id ? " (parent: {$c->parent_id})" : " [ROOT]") . "\n";
}

echo "\n=== LISTINGS vs CATEGORIES ===\n";
$listings = \DB::table('marketplace_listings')->whereNull('deleted_at')->where('status','ACTIVE')->get(['id','title','category']);
foreach ($listings as $l) {
    $exists = \App\Models\MarketplaceCategory::where('name', $l->category)->exists();
    echo "  [{$l->id}] '{$l->category}' => " . ($exists ? "✓ MATCH" : "✗ NO MATCH") . "\n";
}

echo "\n=== ROOT CATEGORIES total_listings_count ===\n";
$roots = \App\Models\MarketplaceCategory::whereNull('parent_id')->orderBy('order_index')->get();
foreach ($roots as $r) {
    echo "  {$r->label}: {$r->total_listings_count} annonce(s)\n";
}
