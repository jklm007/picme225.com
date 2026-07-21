<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\MarketplaceListing;
use Illuminate\Support\Facades\Hash;

// 1. Create a dummy user if none exists
$user = User::first();
if (!$user) {
    echo "Creating dummy seller user...\n";
    $user = User::create([
        'first_name' => 'Demo',
        'last_name' => 'Seller',
        'email' => 'seller@picme225.com',
        'password' => Hash::make('password'),
        'mobile' => '+2250707070707',
        'payment_mode' => 'CASH',
        'is_verified' => true,
    ]);
}

// 2. Clear existing listings
echo "Clearing existing marketplace listings...\n";
MarketplaceListing::query()->delete();

// 3. Create dummy listings
$listings = [
    [
        'category' => 'VEHICLES',
        'type' => 'RENTAL',
        'title' => 'Toyota Land Cruiser V8 (2023)',
        'description' => 'Splendide SUV Toyota Land Cruiser V8 disponible pour location avec chauffeur à Abidjan. Climatisation d\'origine, intérieur cuir luxueux, idéal pour délégations ou cortèges officiels.',
        'price' => 150000.00,
        'price_unit' => 'day',
        'cover_image' => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=600&q=80',
        'location_city' => 'Abidjan (Cocody)',
        'owner_name' => 'Aristide K.',
        'owner_phone' => '+2250707070707',
        'pickup_address' => 'Cocody Riviera 3, Abidjan',
        'status' => 'ACTIVE',
    ],
    [
        'category' => 'VEHICLES',
        'type' => 'RENTAL',
        'title' => 'Hyundai Tucson (2022)',
        'description' => 'SUV urbain Hyundai Tucson boîte automatique, climatisation d\'origine, essence. Disponible pour trajets urbains à Abidjan. Option avec ou sans chauffeur.',
        'price' => 45000.00,
        'price_unit' => 'day',
        'cover_image' => 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=600&q=80',
        'location_city' => 'Abidjan (Marcory)',
        'owner_name' => 'Mireille T.',
        'owner_phone' => '+2250505050505',
        'pickup_address' => 'Marcory Zone 4, Abidjan',
        'status' => 'ACTIVE',
    ],
    [
        'category' => 'ARTICLE',
        'type' => 'SALE',
        'title' => 'PlayStation 5 Slim 1To',
        'description' => 'Console PS5 Slim flambant neuve dans son carton avec 2 manettes DualSense d\'origine et le jeu EA Sports FC 24. Garantie 12 mois constructeur.',
        'price' => 380000.00,
        'price_unit' => 'piece',
        'cover_image' => 'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?auto=format&fit=crop&w=600&q=80',
        'location_city' => 'Abidjan (Yopougon)',
        'owner_name' => 'Marc-Antoine Y.',
        'owner_phone' => '+2250101010101',
        'pickup_address' => 'Yopougon Maroc, Abidjan',
        'status' => 'ACTIVE',
    ],
    [
        'category' => 'REAL_ESTATE',
        'type' => 'RENTAL',
        'title' => 'Studio Meublé Chic à Cocody Angré',
        'description' => 'Studio entièrement équipé et meublé de haut standing avec climatisation, Wi-Fi haut débit, Smart TV (Netflix/Canal+), chauffe-eau, sécurité 24h/24.',
        'price' => 25000.00,
        'price_unit' => 'day',
        'cover_image' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=600&q=80',
        'location_city' => 'Abidjan (Cocody)',
        'owner_name' => 'Sarah B.',
        'owner_phone' => '+2250909090909',
        'pickup_address' => 'Cocody Angré Nouveau CHU, Abidjan',
        'status' => 'ACTIVE',
    ],
    [
        'category' => 'TICKETS',
        'type' => 'SALE',
        'title' => 'Concert Géant de Fally Ipupa à Abidjan',
        'description' => 'Ticket Grand Public pour le concert événement de Fally Ipupa à l\'Esplanade du Palais de la Culture de Treichville le 28 Décembre prochain.',
        'price' => 15000.00,
        'price_unit' => 'ticket',
        'cover_image' => 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=600&q=80',
        'location_city' => 'Abidjan (Treichville)',
        'owner_name' => 'PickMe Events',
        'owner_phone' => '+2250202020202',
        'pickup_address' => 'Palais de la Culture, Treichville, Abidjan',
        'status' => 'ACTIVE',
    ],
    [
        'category' => 'SERVICES',
        'type' => 'RENTAL',
        'title' => 'Service de Chauffeur VTC Privé VIP',
        'description' => 'Chauffeur professionnel bilingue disponible pour accompagnements professionnels, transferts aéroport ou déplacements VIP. Habillé en costume, discret et ponctuel.',
        'price' => 30000.00,
        'price_unit' => 'day',
        'cover_image' => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=600&q=80',
        'location_city' => 'Abidjan (Cocody)',
        'owner_name' => 'Vianney K.',
        'owner_phone' => '+2250303030303',
        'pickup_address' => 'Cocody Ambassades, Abidjan',
        'status' => 'ACTIVE',
    ],
];

foreach ($listings as $listingData) {
    $listingData['user_id'] = $user->id;
    $listingData['images'] = [$listingData['cover_image']];
    MarketplaceListing::create($listingData);
    echo "Created listing: {$listingData['title']}\n";
}

echo "Total listings in DB now: " . MarketplaceListing::count() . "\n";
