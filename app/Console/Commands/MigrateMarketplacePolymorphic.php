<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MarketplaceListing;

class MigrateMarketplacePolymorphic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:migrate-polymorphic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate old marketplace listings to their new polymorphic tables using the observer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting historical polymorphic migration...');

        // We only migrate those that don't have a listable_id yet
        $query = MarketplaceListing::whereNull('listable_id');
        $total = $query->count();
        
        if ($total === 0) {
            $this->info('No listings need migration. All caught up!');
            return 0;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunkById(500, function ($listings) use ($bar) {
            foreach ($listings as $listing) {
                // By simply saving the model, we trigger the saved event which triggers our observer.
                // We use touch() to update updated_at and fire the saved event without modifying other fields,
                // or just trigger save(). But since no fields changed, save() might not fire 'saved' if it's not dirty.
                // Alternatively we can dispatch the event manually or trigger an update.
                
                // Fire the saved event manually to let Observer do its job
                event('eloquent.saved: App\Models\MarketplaceListing', $listing);
                
                $bar->advance();
            }
        });

        $bar->finish();
        $this->info("\nMigration completed successfully for {$total} listings.");

        return 0;
    }
}
