<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\MarketplaceListing;
use App\Models\EventPassType;
use App\Models\SecureMessage;
use Carbon\Carbon;

class MarketplaceCleanAndSeedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info("Début de la purge des tables de Marketplace et de Messagerie...");

        // 1. Désactivation temporaire des contraintes de clés étrangères
        if (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'replica';"); } else { \DB::statement('SET FOREIGN_KEY_CHECKS=0;'); }

        // 2. Nettoyage complet (troncature) des tables
        DB::table('secure_messages')->truncate();
        DB::table('rental_bookings')->truncate();
        DB::table('marketplace_ratings')->truncate();
        DB::table('event_pass_types')->truncate();
        DB::table('transport_tickets')->truncate();
        DB::table('marketplace_agents')->truncate();
        DB::table('intentions')->truncate();
        DB::table('marketplace_listings')->truncate();
        
        // Nettoyage des tables polymorphes spécialisées
        DB::table('mkt_real_estates')->truncate();
        DB::table('mkt_vehicles')->truncate();
        DB::table('mkt_logistics')->truncate();
        DB::table('mkt_events')->truncate();
        DB::table('mkt_services')->truncate();
        DB::table('mkt_products')->truncate();

        // 3. Réactivation des contraintes de clés étrangères
        if (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'origin';"); } else { \DB::statement('SET FOREIGN_KEY_CHECKS=1;'); }
        $this->command->info("Purge terminée !");

        // 4. Initialisation des catégories
        $this->command->info("Initialisation des catégories du Marketplace...");
        $this->call(MarketplaceCategorySeeder::class);

        // 5. Création/Mise à jour de l'utilisateur émetteur (Vendeur JKLM)
        $this->command->info("Initialisation des utilisateurs de test...");
        
        $seller = User::where('display_name', 'JKLM')
            ->orWhere('email', 'antoine@picme.com')
            ->first();

        if (!$seller) {
            // Tenter de réutiliser l'ID 1 pour éviter d'invalider les sessions existantes
            $seller = User::find(1);
            if ($seller) {
                $seller->update([
                    'first_name' => 'Antoine',
                    'last_name' => 'Kouakou',
                    'display_name' => 'JKLM',
                    'email' => 'antoine@picme.com',
                ]);
            } else {
                $seller = User::create([
                    'id' => 1,
                    'first_name' => 'Antoine',
                    'last_name' => 'Kouakou',
                    'display_name' => 'JKLM',
                    'email' => 'antoine@picme.com',
                    'mobile' => '+2250102030405',
                    'password' => Hash::make('123456'),
                    'user_type' => 'USER',
                    'payment_mode' => 'CASH',
                    'device_type' => 'android',
                    'login_by' => 'manual',
                ]);
            }
        } else {
            $seller->update([
                'first_name' => 'Antoine',
                'last_name' => 'Kouakou',
                'display_name' => 'JKLM',
            ]);
        }

        // Créer un acheteur test de discussion
        $buyer = User::where('email', 'buyer@picme.com')->first();
        if (!$buyer) {
            $buyer = User::create([
                'first_name' => 'Acheteur',
                'last_name' => 'Test',
                'display_name' => 'AcheteurTest',
                'email' => 'buyer@picme.com',
                'mobile' => '+2250102030499',
                'password' => Hash::make('123456'),
                'user_type' => 'USER',
                'payment_mode' => 'CASH',
                'device_type' => 'android',
                'login_by' => 'manual',
            ]);
        }

        $this->command->info("Vendeur principal : Antoine Kouakou (JKLM) - ID: {$seller->id}");
        $this->command->info("Acheteur de test : Acheteur Test - ID: {$buyer->id}");

        // 6. Création des annonces par JKLM
        $this->command->info("Génération des nouvelles annonces polymorphes...");

        // Image fallbacks réalistes (Unsplash)
        $imgImmo = 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=500';
        $imgCar = 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?w=500';
        $imgTicket = 'https://images.unsplash.com/photo-1540039155732-d68a2ee3be4f?w=500';
        $imgPhone = 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500';
        $imgService = 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=500';
        $imgFood = 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=500';

        // --- REAL_ESTATE ---
        $listingImmo = MarketplaceListing::create([
            'user_id' => $seller->id,
            'type' => 'RENTAL',
            'category' => 'REAL_ESTATE_LOCATION_MAISON',
            'title' => 'Splendide Villa Duplex 5 Pièces',
            'description' => 'Magnifique duplex avec piscine et grand jardin situé dans une zone sécurisée de la Riviera Faya. Garage disponible pour 2 voitures, grand séjour lumineux et finitions modernes.',
            'price' => 150000,
            'cover_image' => $imgImmo,
            'images' => [$imgImmo, $imgImmo],
            'status' => 'ACTIVE',
            'owner_name' => $seller->first_name . ' ' . $seller->last_name,
            'owner_phone' => $seller->mobile,
            // Attributs virtuels interceptés par l'observer
            'location_city' => 'Abidjan, Riviera Faya',
            'location_latitude' => 5.3621,
            'location_longitude' => -3.9312,
            'price_unit' => 'month',
        ]);
        $this->command->info("-> Annonce Immobilière créée (ID: {$listingImmo->id})");

        // --- VEHICLES ---
        $listingCar = MarketplaceListing::create([
            'user_id' => $seller->id,
            'type' => 'RENTAL',
            'category' => 'VEHICLES_LOCATION_VEHICULE',
            'title' => 'Toyota Prado TXL 2023 Climatisée',
            'description' => 'Profitez de notre Toyota Prado haut standing pour vos déplacements d\'affaires, événements ou cérémonies à Abidjan et à l\'intérieur du pays. Voiture très confortable et propre.',
            'price' => 85000,
            'cover_image' => $imgCar,
            'images' => [$imgCar, $imgCar],
            'status' => 'ACTIVE',
            'owner_name' => $seller->first_name . ' ' . $seller->last_name,
            'owner_phone' => $seller->mobile,
            // Attributs virtuels
            'brand' => 'Toyota',
            'model' => 'Prado TXL',
            'year' => '2023',
            'color' => 'Noir',
            'plate_number' => '123456-CI',
            'with_driver' => true,
            'driver_price' => 15000,
            'driving_policy' => 'Chauffeur inclus obligatoirement pour les trajets interurbains.',
            'price_unit' => 'day',
        ]);
        $this->command->info("-> Annonce Véhicule créée (ID: {$listingCar->id})");

        // --- TICKETS ---
        $listingTicket = MarketplaceListing::create([
            'user_id' => $seller->id,
            'type' => 'SALE',
            'category' => 'TICKETS_CONCERT',
            'title' => 'Festival Live de l\'Indépendance 2026',
            'description' => 'Participez au plus grand concert célébrant l\'Indépendance avec plusieurs artistes nationaux et internationaux en tête d\'affiche au Palais de la Culture.',
            'price' => 5000,
            'cover_image' => $imgTicket,
            'images' => [$imgTicket, $imgTicket],
            'status' => 'ACTIVE',
            'owner_name' => $seller->first_name . ' ' . $seller->last_name,
            'owner_phone' => $seller->mobile,
        ]);

        // Créer les pass associés pour les tickets (Indispensable pour l'achat de tickets dans l'app)
        EventPassType::create([
            'listing_id' => $listingTicket->id,
            'name' => 'Pass Standard',
            'price' => 5000,
            'valid_from' => '18:00:00',
            'valid_until' => '23:59:59',
            'quantity' => 1000,
            'sold_count' => 15,
            'persons_per_pass' => 1
        ]);

        EventPassType::create([
            'listing_id' => $listingTicket->id,
            'name' => 'Pass VIP',
            'price' => 20000,
            'valid_from' => '18:00:00',
            'valid_until' => '23:59:59',
            'quantity' => 200,
            'sold_count' => 5,
            'persons_per_pass' => 1
        ]);
        $this->command->info("-> Annonce Ticket créée avec Passes associés (ID: {$listingTicket->id})");

        // --- SERVICES ---
        $listingService = MarketplaceListing::create([
            'user_id' => $seller->id,
            'type' => 'SALE',
            'category' => 'SERVICES_DEVELOPPEUR',
            'title' => 'Création d\'Applications Mobiles Android & iOS',
            'description' => 'Développeur expérimenté disponible pour concevoir vos applications mobiles professionnelles. Intégration de paiement Wave/MTN/Orange, API Laravel et Firebase en temps réel.',
            'price' => 350000,
            'cover_image' => $imgService,
            'images' => [$imgService],
            'status' => 'ACTIVE',
            'owner_name' => $seller->first_name . ' ' . $seller->last_name,
            'owner_phone' => $seller->mobile,
            // Attributs virtuels
            'price_unit' => 'project',
        ]);
        $this->command->info("-> Annonce Service créée (ID: {$listingService->id})");

        // --- SALE / ELECTRONICS ---
        $listingProduct = MarketplaceListing::create([
            'user_id' => $seller->id,
            'type' => 'SALE',
            'category' => 'ELECTRONICS_TELEPHONES',
            'title' => 'iPhone 15 Pro Max 512Go Neuf',
            'description' => 'iPhone 15 Pro Max 512Go original, scellé dans son carton d\'origine. Garantie Apple de 1 an. Vendu avec facture normalisée.',
            'price' => 900000,
            'cover_image' => $imgPhone,
            'images' => [$imgPhone, $imgPhone],
            'status' => 'ACTIVE',
            'owner_name' => $seller->first_name . ' ' . $seller->last_name,
            'owner_phone' => $seller->mobile,
            // Attributs virtuels
            'stock_quantity' => 4,
            'home_delivery' => true,
            'delivery_price' => 2500,
            'is_digital' => false,
        ]);
        $this->command->info("-> Annonce Produit Physique créée (ID: {$listingProduct->id})");

        // --- FOOD ---
        $listingFood = MarketplaceListing::create([
            'user_id' => $seller->id,
            'type' => 'SALE',
            'category' => 'FOOD_RESTAURANT',
            'title' => 'Garba Premium Poisson Frit',
            'description' => 'Commandez notre garba premium cuisiné avec soin. Attiéké de qualité supérieure, poisson thon frais frit et piments bien épicés. Idéal pour votre déjeuner.',
            'price' => 3000,
            'cover_image' => $imgFood,
            'images' => [$imgFood],
            'status' => 'ACTIVE',
            'owner_name' => $seller->first_name . ' ' . $seller->last_name,
            'owner_phone' => $seller->mobile,
            // Attributs virtuels
            'stock_quantity' => 50,
            'home_delivery' => true,
            'delivery_price' => 1000,
            'is_digital' => false,
        ]);
        $this->command->info("-> Annonce Alimentaire créée (ID: {$listingFood->id})");

        // 7. Création de messages de discussion sécurisée simulés
        $this->command->info("Génération de l'historique des discussions de test...");

        // Discussion autour de l'iPhone 15 Pro Max
        $messages = [
            [
                'sender_id' => $buyer->id,
                'receiver_id' => $seller->id,
                'message' => "Bonjour JKLM, l'iPhone 15 Pro Max de 512Go est-il toujours disponible ?",
            ],
            [
                'sender_id' => $seller->id,
                'receiver_id' => $buyer->id,
                'message' => "Bonjour ! Oui, le téléphone est toujours disponible et scellé. C'est le dernier en stock.",
            ],
            [
                'sender_id' => $buyer->id,
                'receiver_id' => $seller->id,
                'message' => "Super ! Est-il possible d'être livré aujourd'hui à Cocody Angré ?",
            ],
            [
                'sender_id' => $seller->id,
                'receiver_id' => $buyer->id,
                'message' => "Oui, bien sûr. Notre livreur peut passer entre 16h et 18h. Le coût de livraison est de 2 500 FCFA.",
            ],
            [
                'sender_id' => $buyer->id,
                'receiver_id' => $seller->id,
                'message' => "C'est d'accord pour moi. Je vous envoie mon adresse exacte par SMS.",
            ]
        ];

        foreach ($messages as $index => $msgData) {
            SecureMessage::create([
                'sender_type' => 'user',
                'sender_id' => $msgData['sender_id'],
                'receiver_type' => 'user',
                'receiver_id' => $msgData['receiver_id'],
                'announcement_id' => $listingProduct->id, // iPhone
                'message' => $msgData['message'],
                'is_flagged' => false,
                'is_blocked' => false,
                'lead_score' => 'WARM',
                'ai_used' => false,
                'created_at' => Carbon::now()->subMinutes(10 - $index),
            ]);
        }
        $this->command->info("-> Discussion de test créée autour de l'iPhone !");

        $this->command->info("Seeder exécuté avec succès. Toutes les données du Marketplace respectent vos contraintes !");
    }
}
