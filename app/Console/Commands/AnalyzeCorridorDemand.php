<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use App\Models\PostPledge;
use App\Models\PdpRoute;

class AnalyzeCorridorDemand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'social:analyze-demand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze community transport intentions and dispatch profit opportunities to drivers.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🚀 Analyzing Corridor Demand...");

        // 1. Group active pledges by corridor
        $demands = PostPledge::where('status', 'PLEDGED')
            ->where('created_at', '>=', now()->subHours(12))
            ->get()
            ->groupBy(function($item) {
                return $item->post->pdp_route_id;
            });

        foreach ($demands as $routeId => $pledges) {
            $count = $pledges->count();
            if ($count >= 2) { // Low threshold for early testing, usually 4+
                $route = PdpRoute::find($routeId);
                $routeName = $route ? $route->title : "Corridor Local";

                // Generate a "Profit Opportunity" post for Drivers
                Post::create([
                    'user_id'      => null,
                    'type'         => 'OPPORTUNITY',
                    'source'       => 'PicMe Info',
                    'category'     => 'OPPORTUNITY',
                    'content'      => "🔥 OPPORTUNITÉ : Forte demande sur le corridor [$routeName]. $count passagers cherchent un départ immédiat. Chauffeurs, positionnez-vous !",
                    'pdp_route_id' => $routeId,
                    'published_at' => now(),
                    'status'       => 'ACTIVE'
                ]);

                $this->info("✅ Dispatching opportunity for corridor #$routeId ($count pledges)");
            }
        }

        $this->info("🎯 Demand analysis complete.");
    }
}
