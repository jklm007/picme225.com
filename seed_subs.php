<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MarketplaceCategory;
use App\Models\MarketplaceListing;

$cats = [
    'VEHICLES' => ['label' => 'Véhicules', 'icon' => '🚗', 'subs' => ['Voitures', 'Motos', 'Utilitaires', 'Location', 'Pièces Auto']],
    'ARTICLE' => ['label' => 'Articles & Vente', 'icon' => '📦', 'subs' => ['Électronique', 'Mode', 'Maison', 'Alimentation', 'Beauté']],
    'REAL_ESTATE' => ['label' => 'Immobilier', 'icon' => '🏠', 'subs' => ['Appartements', 'Villas', 'Terrains', 'Bureaux', 'Location Courte Durée']],
    'TICKETS' => ['label' => 'Billets & Événements', 'icon' => '🎫', 'subs' => ['Concerts', 'Voyages', 'Cinéma', 'Sport', 'VIP']],
    'SERVICES' => ['label' => 'Services & Emploi', 'icon' => '🛠️', 'subs' => ['Bricolage', 'Ménage', 'Soutien Scolaire', 'Transport', 'Offres d\'emploi']],
];

$i = 1;
foreach ($cats as $key => $data) {
    // Main category
    $main = MarketplaceCategory::updateOrCreate(
        ['name' => $key],
        ['label' => $data['label'], 'icon' => $data['icon'], 'order_index' => $i, 'parent_id' => null]
    );
    
    // Subcategories
    $j = 1;
    foreach ($data['subs'] as $sub) {
        MarketplaceCategory::updateOrCreate(
            ['name' => $key . '_' . strtoupper(str_replace([' ', "'", 'é', 'è'], ['_', '', 'E', 'E'], $sub))],
            ['label' => $sub, 'parent_id' => $main->id, 'order_index' => $j]
        );
        $j++;
    }
    $i++;
}

// Relier les annonces existantes aux catégories principales
$listings = MarketplaceListing::all();
foreach ($listings as $listing) {
    $c = strtolower($listing->category);
    if (strpos($c, 'véhicule') !== false || strpos($c, 'voiture') !== false || strpos($c, 'auto') !== false || strpos($c, 'vehicles') !== false) {
        $listing->category = 'VEHICLES';
    } elseif (strpos($c, 'immo') !== false || strpos($c, 'real_estate') !== false) {
        $listing->category = 'REAL_ESTATE';
    } elseif (strpos($c, 'service') !== false || strpos($c, 'emploi') !== false || strpos($c, 'services') !== false) {
        $listing->category = 'SERVICES';
    } elseif (strpos($c, 'billet') !== false || strpos($c, 'event') !== false || strpos($c, 'tickets') !== false) {
        $listing->category = 'TICKETS';
    } else {
        $listing->category = 'ARTICLE';
    }
    $listing->save();
}

echo "Catégories et sous-catégories créées, et annonces rattachées !\n";
