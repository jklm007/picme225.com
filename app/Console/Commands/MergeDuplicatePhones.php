<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Provider;
use App\Helpers\PhoneHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MergeDuplicatePhones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:merge-phones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalize phone numbers and merge duplicate user/provider accounts (keeps the oldest).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Démarrage de la normalisation et fusion des numéros...");

        $this->mergeUsers();
        $this->mergeProviders();

        $this->info("Terminé.");
    }

    private function mergeUsers()
    {
        $this->info("Nettoyage de la table USERS...");
        $users = User::orderBy('created_at', 'asc')->get();
        $normalizedMap = [];

        foreach ($users as $user) {
            $raw = $user->mobile;
            $normalized = PhoneHelper::normalize($raw);

            // Mettre à jour le numéro si nécessaire
            if ($raw !== $normalized && !empty($normalized)) {
                // S'assurer de ne pas planter sur une contrainte unique
                $exists = User::where('mobile', $normalized)->where('id', '!=', $user->id)->first();
                if (!$exists) {
                    $user->mobile = $normalized;
                    $user->save();
                    $this->line("User #{$user->id} normalisé : {$raw} => {$normalized}");
                }
            }

            if (empty($normalized)) continue;

            if (!isset($normalizedMap[$normalized])) {
                $normalizedMap[$normalized] = $user;
            } else {
                $mainUser = $normalizedMap[$normalized];
                $duplicate = $user; // C'est le plus récent vu qu'on a trié par created_at asc

                $this->warn("Doublon trouvé pour User {$normalized}: Keep #{$mainUser->id}, Merge/Delete #{$duplicate->id}");

                DB::beginTransaction();
                try {
                    // 1. Transférer le solde
                    $mainUser->wallet_balance += $duplicate->wallet_balance;
                    $mainUser->save();

                    // 2. Transférer les courses
                    DB::table('user_requests')->where('user_id', $duplicate->id)->update(['user_id' => $mainUser->id]);
                    DB::table('user_request_ratings')->where('user_id', $duplicate->id)->update(['user_id' => $mainUser->id]);

                    // 3. Transférer l'historique du portefeuille
                    DB::table('wallet_passbooks')->where('user_id', $duplicate->id)->update(['user_id' => $mainUser->id]);

                    // 4. Supprimer le doublon
                    $duplicate->delete();

                    DB::commit();
                    $this->info("✅ Fusion réussie pour {$normalized}");
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("❌ Échec de la fusion pour {$normalized}: " . $e->getMessage());
                }
            }
        }
    }

    private function mergeProviders()
    {
        $this->info("Nettoyage de la table PROVIDERS...");
        $providers = Provider::orderBy('created_at', 'asc')->get();
        $normalizedMap = [];

        foreach ($providers as $provider) {
            $raw = $provider->mobile;
            $normalized = PhoneHelper::normalize($raw);

            // Mettre à jour le numéro
            if ($raw !== $normalized && !empty($normalized)) {
                $exists = Provider::where('mobile', $normalized)->where('id', '!=', $provider->id)->first();
                if (!$exists) {
                    $provider->mobile = $normalized;
                    $provider->save();
                    $this->line("Provider #{$provider->id} normalisé : {$raw} => {$normalized}");
                }
            }

            if (empty($normalized)) continue;

            if (!isset($normalizedMap[$normalized])) {
                $normalizedMap[$normalized] = $provider;
            } else {
                $mainProvider = $normalizedMap[$normalized];
                $duplicate = $provider;

                $this->warn("Doublon trouvé pour Provider {$normalized}: Keep #{$mainProvider->id}, Merge/Delete #{$duplicate->id}");

                DB::beginTransaction();
                try {
                    // 1. Transférer le solde
                    $mainProvider->wallet_balance += $duplicate->wallet_balance;
                    $mainProvider->save();

                    // 2. Transférer les courses
                    DB::table('user_requests')->where('provider_id', $duplicate->id)->update(['provider_id' => $mainProvider->id]);
                    DB::table('user_request_ratings')->where('provider_id', $duplicate->id)->update(['provider_id' => $mainProvider->id]);

                    // 3. Transférer l'historique du portefeuille
                    DB::table('wallet_passbooks')->where('user_id', $duplicate->id)->where('user_type', 'PROVIDER')->update(['user_id' => $mainProvider->id]);
                    
                    // 4. Supprimer les services du doublon pour éviter les conflits
                    DB::table('provider_services')->where('provider_id', $duplicate->id)->delete();
                    DB::table('provider_devices')->where('provider_id', $duplicate->id)->delete();

                    // 5. Supprimer le doublon
                    $duplicate->delete();

                    DB::commit();
                    $this->info("✅ Fusion réussie pour {$normalized}");
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("❌ Échec de la fusion pour {$normalized}: " . $e->getMessage());
                }
            }
        }
    }
}
