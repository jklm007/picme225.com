<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SocialTransportSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $users = DB::table('users')->limit(10)->pluck('id')->toArray();

        if (empty($users)) {
            $this->command->warn('Aucun utilisateur trouvé.');
            return;
        }

        $posts = [
            // ──── TRAJETS (TRIP) ────
            [
                'content'         => '🚗 Je pars d\'Abobo vers le Plateau à 7h30. Places disponibles, trajet rapide via l\'autoroute. Musicien calme bienvenu.',
                'category'        => 'TRIP',
                'seats_available' => 3,
                'pledge_threshold'=> 4,
            ],
            [
                'content'         => '🚗 Départ Yopougon Selmer → Deux Plateaux Vallon. Départ 8h pile. Maximum 2 personnes. Partage d\'essence 500 FCFA.',
                'category'        => 'TRIP',
                'seats_available' => 2,
                'pledge_threshold'=> 3,
            ],
            [
                'content'         => '🚙 Cocody Riviera 3 → Zone 4 Marcory. Départ 9h. Tarif 600 FCFA. Voiture climatisée. Ponctuel garanti.',
                'category'        => 'TRIP',
                'seats_available' => 4,
                'pledge_threshold'=> 4,
            ],
            [
                'content'         => '🚗 Adjamé liberté → Angré Château. Départ dans 30 min. 3 places. 700 FCFA/personne.',
                'category'        => 'TRIP',
                'seats_available' => 3,
                'pledge_threshold'=> 3,
            ],
            [
                'content'         => '🚗 Grand Bassam → Abidjan Plateau. Départ 7h00. Trajet confortable, 2000 FCFA. Je fais ce trajet tous les lundis.',
                'category'        => 'TRIP',
                'seats_available' => 2,
                'pledge_threshold'=> 3,
            ],
            [
                'content'         => '🚌 Bingerville → Cocody. Départ 6h45. 3 places disponibles. 1000 FCFA. Pas de détour.',
                'category'        => 'TRIP',
                'seats_available' => 3,
                'pledge_threshold'=> 4,
            ],
            [
                'content'         => '🚗 Williamsville → Treichville marché. Départ maintenant. 2 places. 400 FCFA.',
                'category'        => 'TRIP',
                'seats_available' => 2,
                'pledge_threshold'=> 3,
            ],
            // ──── INTENTIONS ────
            [
                'content'         => '📣 Cherche covoiturage Yopougon → Plateau tous les matins entre 7h et 8h. Je partage les frais. Qui est intéressé ?',
                'category'        => 'INTENTION',
                'seats_available' => 1,
                'pledge_threshold'=> 4,
            ],
            [
                'content'         => '📣 [DEMANDE] Je cherche un trajet depuis Angré vers Treichville chaque jour ouvrable. Environ 8h00. Budget 600 FCFA.',
                'category'        => 'INTENTION',
                'seats_available' => 1,
                'pledge_threshold'=> 3,
            ],
            [
                'content'         => '📣 Besoin d\'un trajet Cocody II Plateaux → Zone 4 le vendredi soir vers 18h. Quelqu\'un fait ce trajet régulièrement ?',
                'category'        => 'INTENTION',
                'seats_available' => 1,
                'pledge_threshold'=> 5,
            ],
            [
                'content'         => '📣 [GROUPE] On est 4 collègues à Abobo Baoulé. On cherche un chauffeur régulier vers Marcory Biétry. Lun-Ven 7h15.',
                'category'        => 'INTENTION',
                'seats_available' => 4,
                'pledge_threshold'=> 4,
            ],
            [
                'content'         => '📣 Je cherche un covoiturage Grand-Bassam → Abidjan le dimanche soir. Vers 19h-20h. Partage des frais d\'essence.',
                'category'        => 'INTENTION',
                'seats_available' => 1,
                'pledge_threshold'=> 3,
            ],
            [
                'content'         => '📣 [DEMANDE] Trajet régulier Riviera Palmeraie → Plateau. Je cherche quelqu\'un qui fait ce trajet quotidiennement.',
                'category'        => 'INTENTION',
                'seats_available' => 1,
                'pledge_threshold'=> 4,
            ],
        ];

        $this->command->info('Insertion de ' . count($posts) . ' posts sociaux dans la table posts...');
        $inserted = 0;

        foreach ($posts as $post) {
            $userId = $users[array_rand($users)];
            DB::table('posts')->insert([
                'user_id'         => $userId,
                'content'         => $post['content'],
                'type'            => $post['category'], // TRIP or INTENTION
                'source'          => 'INTERNAL',
                'category'        => 'TRANSPORT',
                'seats_available' => $post['seats_available'],
                'pledge_threshold'=> $post['pledge_threshold'],
                'pledge_count'    => rand(0, $post['pledge_threshold'] - 1),
                'likes_count'     => rand(0, 30),
                'comments_count'  => rand(0, 10),
                'status'          => 'ACTIVE',
                'created_at'      => $now->copy()->subMinutes(rand(5, 600)),
                'updated_at'      => $now,
            ]);
            $inserted++;
        }

        $this->command->info("✅ {$inserted} posts créés avec succès !");
    }
}
