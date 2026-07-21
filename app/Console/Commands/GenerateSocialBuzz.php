<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSocialBuzz extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'social:buzz {count=5}';
    protected $description = 'Génère du contenu automatisé (News, Sondages, Buzz) pour le fil social';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->argument('count');
        $corridors = \App\Models\PdpRoute::all();
        
        $this->info("Génération de $count news sociales...");

        for ($i = 0; $i < $count; $i++) {
            $type = collect(['NEWS', 'VIRAL', 'POLL'])->random();
            $corridor = $corridors->random();
            
            if ($type === 'POLL') {
                $this->createPoll($corridor);
            } else {
                $this->createNews($corridor, $type);
            }
        }

        $this->info("Buzz généré avec succès !");
    }

    private function createNews($corridor, $type)
    {
        $news = [
            'TRAFFIC' => [
                "⚠️ Ralentissement signalé sur le tronçon {$corridor->name}. Évitez le secteur si possible.",
                "✅ Circulation fluide sur {$corridor->name}. Bon voyage à tous !",
                "🚜 Travaux en cours sur {$corridor->name}. Prévoyez 15 min de retard.",
            ],
            'ACCIDENT' => [
                "🚨 Accident léger signalé sur {$corridor->name}. La police est sur place.",
                "⚠️ Attention : Camion en panne sur la voie de droite sur {$corridor->name}.",
            ],
            'BUZZ' => [
                "🔥 On annonce une grosse affluence pour le match de demain. Pensez à réserver vos trajets partagés !",
                "🎵 Concert géant ce soir à proximité de {$corridor->name}. Planifiez vos retours en groupe.",
                "💡 Astuce : Les trajets partagés Picme sont 40% moins chers sur l'axe {$corridor->name}.",
            ]
        ];

        $category = array_rand($news);
        $content = collect($news[$category])->random();

        \App\Models\Post::create([
            'type' => $type,
            'source' => 'PicMe Info',
            'category' => $category,
            'content' => $content,
            'pdp_route_id' => $corridor->id,
            'status' => 'ACTIVE',
            'user_id' => null,
            'published_at' => now(),
        ]);
    }

    private function createPoll($corridor)
    {
        $polls = [
            [
                'question' => "Comment trouvez-vous la sécurité sur l'axe {$corridor->name} ?",
                'options'  => ['Excellente', 'Moyenne', 'À améliorer']
            ],
            [
                'question' => "Quel est votre horaire préféré pour voyager sur {$corridor->name} ?",
                'options'  => ['Matin (6h-9h)', 'Midi', 'Soir (17h-20h)']
            ],
            [
                'question' => "Souhaitez-vous plus de points d'arrêt sur {$corridor->name} ?",
                'options'  => ['Oui, absolument', 'Non, c\'est suffisant']
            ]
        ];

        $p = collect($polls)->random();

        $poll = \App\Models\Poll::create([
            'question'  => $p['question'],
            'is_active' => true,
        ]);

        foreach ($p['options'] as $opt) {
            \App\Models\PollOption::create([
                'poll_id' => $poll->id,
                'option_text' => $opt,
                'votes_count' => 0
            ]);
        }

        \App\Models\Post::create([
            'type' => 'POLL',
            'source' => 'PicMe Info',
            'category' => 'COMMUNITY',
            'content' => $p['question'],
            'poll_id' => $poll->id,
            'pdp_route_id' => $corridor->id,
            'status' => 'ACTIVE',
            'user_id' => null,
            'published_at' => now(),
        ]);
    }
}
