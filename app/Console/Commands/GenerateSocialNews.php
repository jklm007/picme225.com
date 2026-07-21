<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;

class GenerateSocialNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'social:generate-news {count=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère du contenu Buzz et Sondages pour le fil social';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->argument('count');
        $corridors = \App\Models\PdpRoute::all();
        
        $this->info("Génération de $count news/sondages en cours...");

        for ($i = 0; $i < $count; $i++) {
            $type = collect(['NEWS', 'VIRAL', 'POLL'])->random();
            $corridor = $corridors->random();
            
            if ($type === 'POLL') {
                $this->createPoll($corridor);
            } else {
                $this->createNews($corridor, $type);
            }
        }

        $this->info("News et Sondages générés avec succès.");
        return 0;
    }

    private function createNews($corridor, $type)
    {
        $newsSet = [
            'TRAFFIC' => [
                "Attention : Ralentissement signalé sur {$corridor->name}. Évitez le secteur.",
                "Circulation fluide sur {$corridor->name}. Bonne route !",
                "Travaux lents sur {$corridor->name}. Prévoyez 20 min de marge.",
            ],
            'ACCIDENT' => [
                "Accident léger sur {$corridor->name}. Services de secours en route.",
                "Attention : Obstacle sur la chaussée sur {$corridor->name}.",
            ],
            'BUZZ' => [
                "Affluence exceptionnelle sur {$corridor->name} ! Pensez au covoiturage Picme.",
                "Festival local près de {$corridor->name} : Réservez vos trajets de retour.",
                "Économie : Partagez votre course sur {$corridor->name} et économisez 1000 FCFA.",
            ]
        ];

        $category = array_rand($newsSet);
        $content = collect($newsSet[$category])->random();

        Post::create([
            'user_id' => null,
            'type' => $type,
            'source' => 'PicMe Info',
            'category' => $category,
            'content' => $content,
            'pdp_route_id' => $corridor->id,
            'published_at' => now(),
            'status' => 'ACTIVE',
        ]);
    }

    private function createPoll($corridor)
    {
        $pollSet = [
            [
                'question' => "Notez la qualité de la route sur {$corridor->name} :",
                'options'  => ['Excellente', 'Moyenne', 'Critique']
            ],
            [
                'question' => "Avez-vous besoin de plus de gares locales sur {$corridor->name} ?",
                'options'  => ['Oui', 'Non', 'À certains points']
            ],
            [
                'question' => "Utilisez-vous Picme régulièrement sur {$corridor->name} ?",
                'options'  => ['Tous les jours', 'Hebdomadaire', 'Rarement']
            ]
        ];

        $pData = collect($pollSet)->random();

        $poll = \App\Models\Poll::create([
            'question'  => $pData['question'],
            'is_active' => true,
        ]);

        foreach ($pData['options'] as $optText) {
            \App\Models\PollOption::create([
                'poll_id' => $poll->id,
                'option_text' => $optText,
                'votes_count' => 0
            ]);
        }

        Post::create([
            'user_id' => null,
            'type' => 'POLL',
            'source' => 'PicMe Info',
            'category' => 'COMMUNITY',
            'content' => $pData['question'],
            'poll_id' => $poll->id,
            'pdp_route_id' => $corridor->id,
            'published_at' => now(),
            'status' => 'ACTIVE',
        ]);
    }
}
