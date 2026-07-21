<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventTicketsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::first();
        if (!$user) {
            $this->command->info('Aucun utilisateur trouvé. Veuillez créer un utilisateur d\'abord.');
            return;
        }

        $metadata = [
            'event_name' => 'Concert Test: La Grande Nuit',
            'event_date' => '2026-12-31',
            'venue' => 'Stade Félix Houphouët-Boigny, Abidjan',
            'passes' => [] // Optional if we populate EventPassType directly
        ];

        $listing = \App\Models\MarketplaceListing::create([
            'user_id' => $user->id,
            'title' => 'Ticket: Concert Test de fin d\'année',
            'description' => 'Un événement de test pour vérifier la billetterie et le stock de passes.',
            'price' => 5000,
            'category' => 'TICKETS',
            'type' => 'ARTICLE',
            'status' => 'ACTIVE',
            'metadata' => json_encode($metadata),
            'stock_quantity' => 150 // Optionnel pour les tickets, mais on teste quand même
        ]);

        // Créer Pass Standard (1 personne)
        \App\Models\EventPassType::create([
            'listing_id' => $listing->id,
            'name' => 'Pass Standard',
            'price' => 5000,
            'quantity' => 100,
            'sold_count' => 0,
            'valid_from' => '18:00',
            'valid_until' => '23:59',
            'persons_per_pass' => 1
        ]);

        // Créer Pass VIP (5 personnes)
        \App\Models\EventPassType::create([
            'listing_id' => $listing->id,
            'name' => 'Pass VIP (Table)',
            'price' => 50000,
            'quantity' => 50,
            'sold_count' => 0,
            'valid_from' => '18:00',
            'valid_until' => '05:00',
            'persons_per_pass' => 5
        ]);

        // Créer Pass VVIP Epuisé
        \App\Models\EventPassType::create([
            'listing_id' => $listing->id,
            'name' => 'Pass VVIP',
            'price' => 100000,
            'quantity' => 10,
            'sold_count' => 10, // Stock épuisé
            'valid_from' => '18:00',
            'valid_until' => '05:00',
            'persons_per_pass' => 10
        ]);

        $this->command->info("Événement de test créé avec succès ! (Annonce ID: {$listing->id})");
    }
}
