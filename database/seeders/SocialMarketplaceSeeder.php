<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\MarketplaceListing;
use App\Models\User;
use App\Models\PdpRoute;
use App\Models\Poll;
use App\Models\PollOption;
use Carbon\Carbon;

class SocialMarketplaceSeeder extends Seeder
{
    public function run()
    {
        // 1. S'assurer qu'on a au moins un utilisateur pour porter les posts
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'first_name' => 'Agent',
                'last_name' => 'Picme',
                'email' => 'agent@picme.com',
                'mobile' => '+2250102030405',
                'password' => bcrypt('password'),
                'user_type' => 'USER',
                'payment_mode' => 'CASH',
                'device_type' => 'android',
                'login_by' => 'manual',
            ]);
        }

        // 2. Récupérer un corridor pour les trajets (ex: Abidjan-Yamoussoukro)
        $route = PdpRoute::first();

        // --- SECTION 1 : FIL SOCIAL (POSTS) ---

        // 🚗 Un trajet partagé (TRIP)
        Post::create([
            'user_id' => $user->id,
            'type' => 'TRIP',
            'category' => 'TRANSPORT',
            'content' => 'Départ pour Yamoussoukro à 14h. J\'ai 3 places libres dans une climatisée.',
            'pdp_route_id' => $route ? $route->id : null,
            'is_shareable' => true,
            'seats_available' => 3,
            'price' => 5000,
            'status' => 'ACTIVE',
            'expires_at' => Carbon::now()->addHours(5),
            'likes_count' => 12,
            'comments_count' => 3,
        ]);

        // 📣 Une demande de groupe (INTENTION)
        Post::create([
            'user_id' => $user->id,
            'type' => 'INTENTION',
            'category' => 'TRANSPORT',
            'content' => 'Cherche 4 personnes pour partager un VTC vers Bassam ce soir 18h.',
            'pdp_route_id' => $route ? $route->id : null,
            'pledge_count' => 1,
            'pledge_threshold' => 4,
            'status' => 'PLEDGING',
            'expires_at' => Carbon::now()->addHours(3),
        ]);

        // 📰 Une news trafic
        Post::create([
            'user_id' => null, // News système
            'type' => 'NEWS',
            'source' => 'INTERNAL',
            'category' => 'TRAFFIC',
            'content' => '⚠️ Embouteillage important sur le Pont Henri Konan Bédié (HKB) direction Cocody.',
            'media_url' => 'social_media/traffic_alert.jpg',
            'status' => 'ACTIVE',
        ]);

        // 📊 Un sondage (POLL)
        $poll = Poll::create([
            'question' => 'Quel mode de transport préférez-vous pour vos longs trajets ?',
            'expires_at' => Carbon::now()->addDays(7),
        ]);
        PollOption::create(['poll_id' => $poll->id, 'option_text' => 'Covoiturage']);
        PollOption::create(['poll_id' => $poll->id, 'option_text' => 'Mini-car (Gbaka)']);
        PollOption::create(['poll_id' => $poll->id, 'option_text' => 'Bus Officiel']);

        Post::create([
            'user_id' => $user->id,
            'type' => 'POLL',
            'category' => 'COMMUNITY',
            'content' => 'Donnez votre avis sur le transport inter-urbain !',
            'poll_id' => $poll->id,
            'status' => 'ACTIVE',
        ]);


        // --- SECTION 2 : MARKETPLACE (ANNONCES) ---

        // 👗 Vente d'objet
        MarketplaceListing::create([
            'user_id' => $user->id,
            'type' => 'SALE',
            'title' => 'iPhone 15 Pro Max 256GB',
            'description' => 'Neuf dans le carton, jamais utilisé. Garantie 1 an Apple.',
            'price' => 850000,
            'price_unit' => 'FIXED',
            'category' => 'Vente',
            'cover_image' => 'marketplace/iphone.jpg',
            'location_city' => 'Abidjan, Cocody',
            'status' => 'ACTIVE',
        ]);

        // 🔑 Location de véhicule
        MarketplaceListing::create([
            'user_id' => $user->id,
            'type' => 'RENTAL',
            'title' => 'Location Toyota Prado TXL',
            'description' => 'Véhicule tout terrain impeccable pour vos cérémonies ou voyages.',
            'price' => 75000,
            'price_unit' => 'DAY',
            'category' => 'Véhicules',
            'cover_image' => 'marketplace/prado.jpg',
            'with_driver' => true,
            'deposit_amount' => 150000,
            'location_city' => 'Abidjan, Plateau',
            'status' => 'ACTIVE',
        ]);

        // 🏠 Location immobilière
        MarketplaceListing::create([
            'user_id' => $user->id,
            'type' => 'RENTAL',
            'title' => 'Studio meublé - Riviera Faya',
            'description' => 'Magnifique studio climatisé, WiFi haut débit, Canal+, femme de ménage incluse.',
            'price' => 35000,
            'price_unit' => 'DAY',
            'category' => 'Immobilier',
            'cover_image' => 'marketplace/studio_faya.jpg',
            'location_city' => 'Abidjan, Bingerville',
            'status' => 'ACTIVE',
        ]);

        // 🛠️ Service (Dépannage / Plomberie)
        MarketplaceListing::create([
            'user_id' => $user->id,
            'type' => 'SALE',
            'title' => 'Plomberie & Dépannage Rapide',
            'description' => 'Service de plomberie disponible 24h/24. Déplacement en 30 minutes.',
            'price' => 5000,
            'price_unit' => 'FIXED', // Frais de déplacement
            'category' => 'Services',
            'cover_image' => 'marketplace/plumbing.jpg',
            'location_city' => 'Abidjan, Angré',
            'status' => 'ACTIVE',
        ]);

        // 💼 Offre d'emploi (Recrutement)
        MarketplaceListing::create([
            'user_id' => $user->id,
            'type' => 'SALE',
            'title' => 'Recrutement Chauffeur Livreur',
            'description' => 'Besoin de 5 chauffeurs avec permis A et C pour livraison de colis.',
            'price' => 150000, // Salaire indicatif
            'price_unit' => 'MONTH',
            'category' => 'Emploi',
            'cover_image' => 'marketplace/job_offer.jpg',
            'location_city' => 'Abidjan, Koumassi',
            'status' => 'ACTIVE',
        ]);
    }
}
