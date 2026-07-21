<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\MarketplaceListing;
use Carbon\Carbon;

class MarketplaceTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Nettoyage préalable (Optionnel, on supprime seulement les annonces de test)
        MarketplaceListing::where('description', 'like', '%[TEST SEEDER]%')->forceDelete();
        User::where('last_name', 'like', '%_VendorTest%')->forceDelete();

        $firstNames = ['Amadou', 'Marc', 'Awa', 'Jean', 'Koffi', 'Yao'];
        $lastNames = ['Diallo', 'Touré', 'Kouassi', 'Ouattara', 'Kouamé', 'Cissé'];

        // Créer 3 vendeurs factices
        $vendors = [];
        for ($i = 1; $i <= 3; $i++) {
            $fName = $firstNames[array_rand($firstNames)];
            $lName = $lastNames[array_rand($lastNames)];

            $user = User::create([
                'first_name' => $fName,
                'last_name' => $lName . '_VendorTest',
                'email' => "vendor{$i}_test@picme.local",
                'mobile' => "+22501020304" . str_pad($i, 2, '0', STR_PAD_LEFT),
                'password' => Hash::make('123456'),
                'payment_mode' => 'CASH',
                'device_type' => 'android',
                'login_by' => 'manual',
            ]);
            $vendors[] = $user;
        }

        // L'utilisateur principal KOUAKOU (ID 1) pour avoir aussi des annonces
        $mainUser = User::find(1);
        if ($mainUser) {
            $vendors[] = $mainUser;
        }

        $categories = [
            'REAL_ESTATE' => [
                'type' => 'RENTAL',
                'titles' => ['Villa de luxe 4 Pièces', 'Appartement Meublé Cocody', 'Studio chic Zone 4', 'Duplex Haut Standing Riviera'],
                'images' => ['https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=500', 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=500'],
                'metadata' => ['rooms' => 4, 'bathrooms' => 2, 'surface' => 150, 'amenities' => ['Piscine', 'Garage', 'Wifi']]
            ],
            'VEHICLES' => [
                'type' => 'RENTAL',
                'titles' => ['Range Rover Evoque', 'Toyota Corolla 2022', 'Hyundai Tucson', 'Mercedes Benz Classe C'],
                'images' => ['https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?w=500', 'https://images.unsplash.com/photo-1550355291-bbee04a92027?w=500'],
                'metadata' => ['brand' => 'Range Rover', 'model' => 'Evoque', 'year' => 2021, 'transmission' => 'Automatique']
            ],
            'TICKETS' => [
                'type' => 'SALE',
                'titles' => ['Concert Live Yode & Siro', 'Pass VIP Festival des Grillades', 'Match CI vs Sénégal', 'Ticket Soirée Blanche VIP'],
                'images' => ['https://images.unsplash.com/photo-1540039155732-d68a2ee3be4f?w=500', 'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=500'],
                'metadata' => ['event_name' => 'Concert Live', 'event_date' => Carbon::now()->addDays(10)->format('Y-m-d H:i'), 'venue' => 'Palais de la Culture']
            ],
            'ELECTRONICS' => [
                'type' => 'SALE',
                'titles' => ['iPhone 14 Pro Max 256Go', 'MacBook Pro M2 Neuf', 'Samsung S23 Ultra', 'PS5 Edition Standard'],
                'images' => ['https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500', 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=500'],
                'metadata' => ['brand' => 'Apple', 'condition' => 'Neuf', 'warranty' => '12 mois']
            ],
            'FASHION' => [
                'type' => 'SALE',
                'titles' => ['Robe de Soirée Élégante', 'Costume Sur Mesure Bleu', 'Sneakers Nike Air Max', 'Sac à Main de Luxe'],
                'images' => ['https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=500', 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=500'],
                'metadata' => ['size' => 'L', 'color' => 'Bleu', 'condition' => 'Neuf']
            ],
            'SERVICES' => [
                'type' => 'SALE',
                'titles' => ['Plombier Professionnel 24/7', 'Coiffure à Domicile', 'Dépannage Informatique', 'Ménage & Entretien'],
                'images' => ['https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=500', 'https://images.unsplash.com/photo-1527515637-6742512f61a1?w=500'],
                'metadata' => ['service_type' => 'Intervention à domicile']
            ],
        ];

        $count = 0;
        foreach ($categories as $catName => $catData) {
            // Créer 4 annonces par catégorie
            for ($j = 0; $j < 4; $j++) {
                $owner = $vendors[array_rand($vendors)];
                $title = $catData['titles'][array_rand($catData['titles'])];
                $image = $catData['images'][array_rand($catData['images'])];
                $price = rand(50, 2000) * 100;

                MarketplaceListing::create([
                    'user_id' => $owner->id,
                    'type' => $catData['type'],
                    'category' => $catName,
                    'title' => $title,
                    'description' => "Superbe offre disponible immédiatement. Ceci est une annonce de test générée automatiquement. [TEST SEEDER]\n\n" . "Plusieurs détails supplémentaires sur cette offre.",
                    'price' => $price,
                    'price_unit' => $catData['type'] === 'RENTAL' ? 'DAY' : 'FIXED',
                    'cover_image' => $image,
                    'images' => [$image, $image],
                    'status' => 'ACTIVE',
                    'metadata' => $catData['metadata'],
                    'owner_name' => $owner->first_name . ' ' . $owner->last_name,
                    'owner_phone' => $owner->mobile,
                ]);
                $count++;
            }
        }

        $this->command->info("$count annonces de test créées pour " . count($vendors) . " vendeurs !");
    }
}
