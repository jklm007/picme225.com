<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CustomCommand::class,
        \App\Console\Commands\AutoCompleteCashTrips::class,
        \App\Console\Commands\ExpireEcoBonuses::class,
        \App\Console\Commands\SyncDaoTreasury::class,
        \App\Console\Commands\ProcessDaoProposals::class,
        \App\Console\Commands\SyncInsuranceRestitution::class,
        \App\Console\Commands\CheckExpiredSubscriptions::class,
        \App\Console\Commands\SendMonthlyTvaReport::class,
        \App\Console\Commands\CheckTvaDeadline::class,
        \App\Console\Commands\CalculateMonthlyBonuses::class,
        \App\Console\Commands\GenerateSocialNews::class,
        \App\Console\Commands\GenerateSocialBuzz::class,
        \App\Console\Commands\AnalyzeCorridorDemand::class,
        \App\Console\Commands\FetchNews::class,
        \App\Console\Commands\CleanupSocialFeed::class,
        \App\Console\Commands\PackageProject::class,
        \App\Console\Commands\DispatchEngineUpdateScores::class,
        \App\Console\Commands\PrecalculateHeatmapCommand::class,
        \App\Console\Commands\SimulateTripsFlow::class,
        // --- SMS Booking Offline ---
        \App\Console\Commands\ExpirePendingSmsCodes::class,
        // --- Rappels courses planifiées (cloche chauffeur + réassignation) ---
        \App\Console\Commands\ScheduledRideReminderCommand::class,
        \App\Console\Commands\GenerateSubscriptionRides::class,
        \App\Console\Commands\MigrateMarketplacePolymorphic::class,
        \App\Console\Commands\AutoReleaseEscrow::class,
        \App\Console\Commands\SweepProfitsCommand::class,
        \App\Console\Commands\MergeDuplicatePhones::class,
        // ── Subscription Refactor Phase 2 ──────────────────────────────────
        \App\Console\Commands\CheckExpiredSchedules::class,
        \App\Console\Commands\FetchPhotonPdpStops::class,
        \App\Console\Commands\MigrateLegacyPartners::class,
        \App\Console\Commands\CleanupExpiredMarketplaceListings::class,
        \App\Console\Commands\SetEvolutionWebhookCommand::class,
        \App\Console\Commands\MigrateLocalToR2::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('update:rides')->everyMinute();
        $schedule->command('picme:autocomplete-cash-trips')->everyMinute()->withoutOverlapping();

        // --- Rappels progressifs courses planifiées (15min / 5min / 1min) ---
        $schedule->command('rides:remind-scheduled')->everyMinute()->withoutOverlapping();

        // --- SMS Booking : expiration des codes sans réponse (filet de sécurité) ---
        $schedule->command('sms:expire-pending')->everyMinute()->withoutOverlapping();
        $schedule->command('eco:expire-bonuses')->daily();
        $schedule->command('dao:process-proposals')->everyMinute();
        $schedule->command('dao:sync-treasury')->hourly();
        $schedule->command('dao:distribute-insurance-bonus')->monthlyOn(1, '01:00');
        $schedule->command('subscription:check-expiry')->daily();
        
        // Tâches TVA automatisées
        $schedule->command('tva:send-monthly-report')->monthlyOn(1, '09:00'); // 1er du mois à 9h
        $schedule->command('tva:check-deadline')->dailyAt('08:00'); // Tous les jours à 8h
        
        // Bonus mensuels
        $schedule->command('bonuses:calculate-monthly')->monthlyOn(1, '02:00'); // 1er du mois à 2h

        // Flux de news réelles automatisé toutes les 4h
        $schedule->command('news:fetch')->everyFourHours();

        // Analyse prédictive de la demande (Phase 10) - toutes les 30 min
        $schedule->command('social:analyze-demand')->everyThirtyMinutes();

        // Buzz social automatisé (Phase 7) - toutes les 4h (Désactivé pour ne pas avoir de fausses news)
        // $schedule->command('social:buzz')->everyFourHours();

        // Nettoyage régulier des médias expirés (Social Feed) ⌛
        $schedule->command('app:cleanup-social-feed')->daily();

        // Mise à jour des scores IA pour le dispatch
        $schedule->command('dispatch:update-scores')->dailyAt('03:00');

        // [V2.3] Précalcul de la Heatmap de demande pour le moteur IA
        $schedule->command('picme:precalculate-heatmap')->dailyAt('02:30');

        // Libération automatique des séquestres après 72h
        $schedule->command('escrow:auto-release')->hourly();

        // Balayage quotidien des bénéfices vers le compte de charge de l'entreprise
        $schedule->command('profits:sweep')->dailyAt('23:00');

        // Subscription expiry reminders — runs daily at 08:00
        $schedule->job(new \App\Jobs\SubscriptionExpiryReminderJob())->dailyAt('08:00');

        // Check expired commute schedules & marketplace plans — every hour
        $schedule->command('subscription:check-expired-schedules')->hourly()->withoutOverlapping();

        // Generate subscription rides (T-60 minutes look-ahead) — every 15 minutes
        $schedule->command('rides:generate-subscription')->everyFifteenMinutes()->withoutOverlapping();

        // Fleet capacity auto-activation — runs every 15 minutes
        $schedule->job(new \App\Jobs\FleetCapacityAutoActivateJob())->everyFifteenMinutes();

        // Marketplace: Cleanup listings older than 90 days — runs daily at 02:00
        $schedule->command('marketplace:cleanup-expired')->dailyAt('02:00');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}



