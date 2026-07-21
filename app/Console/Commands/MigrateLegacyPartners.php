<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Fleet;
use App\Models\StationAgent;
use App\Models\Partner;
use App\Models\PartnerAffiliate;
use App\Models\Provider;
use App\Models\WalletPassbook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


class MigrateLegacyPartners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:legacy-partners {--simul : Simuler la migration sans modifier la base de données}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migre les anciens comptes de Flottes et d\'Agents de gare vers l\'architecture unifiée de Partenaires';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $simul = $this->option('simul');

        if ($simul) {
            $this->info("=== MODE SIMULATION ACTIVÉ ===");
        }

        DB::beginTransaction();

        try {
            // ==========================================
            // 1. MIGRATION DES FLEET OWNERS
            // ==========================================
            $this->info("Migration des Fleet Owners...");
            $fleets = Fleet::all();

            foreach ($fleets as $fleet) {
                $this->line("Traitement de la Flotte #{$fleet->id} - {$fleet->name}...");

                // Trouver ou créer le compte User lié
                $user = null;
                if ($fleet->user_id) {
                    $user = User::find($fleet->user_id);
                }

                if (!$user) {
                    $query = User::query();
                    $hasQuery = false;
                    if (!empty($fleet->mobile)) {
                        $query->where('mobile', $fleet->mobile);
                        $hasQuery = true;
                    }
                    if (!empty($fleet->email)) {
                        if ($hasQuery) {
                            $query->orWhere('email', $fleet->email);
                        } else {
                            $query->where('email', $fleet->email);
                            $hasQuery = true;
                        }
                    }
                    if ($hasQuery) {
                        $user = $query->first();
                    }
                }

                if (!$user) {
                    if (!$simul) {
                        $mobile = !empty($fleet->mobile) ? $fleet->mobile : '+999' . str_pad($fleet->id, 8, '0', STR_PAD_LEFT);
                        $email = !empty($fleet->email) ? $fleet->email : 'fleet' . $fleet->id . '@partner.local';
                        $password = (isset($fleet->password) && !empty($fleet->password)) ? $fleet->password : bcrypt(Str::random(16));
                        $user = User::create([
                            'first_name' => $fleet->name,
                            'last_name' => 'Owner',
                            'mobile' => $mobile,
                            'email' => $email,
                            'password' => $password,
                            'user_type' => 'PARTNER',
                            'fleet_id' => $fleet->id,
                            'payment_mode' => 'CASH',
                        ]);
                    } else {
                        $this->line("  [SIMUL] Création utilisateur pour flotte {$fleet->name}");
                        $user = new User([
                            'id' => 9999 + $fleet->id,
                            'first_name' => $fleet->name,
                            'wallet_balance' => 0
                        ]);
                    }
                } else {
                    if (!$simul) {
                        $user->update([
                            'user_type' => 'PARTNER',
                            'fleet_id' => $fleet->id
                        ]);
                    }
                }

                if (!$simul && !$fleet->user_id) {
                    $fleet->user_id = $user->id;
                    $fleet->save();
                }

                // Créer le profil Partner
                $partner = null;
                if (!$simul) {
                    $partner = Partner::firstOrCreate(
                        ['user_id' => $user->id, 'type' => 'FLEET_OWNER'],
                        [
                            'partner_code' => 'PRT-F' . str_pad($fleet->id, 5, '0', STR_PAD_LEFT),
                            'status' => 'APPROVED',
                            'tier' => 'STANDARD',
                            'company_name' => $fleet->company,
                            'logo' => $fleet->logo,
                            'commission_rules' => [
                                'trip_share_percent' => 10,
                                'parcel_commission_percent' => 10
                            ]
                        ]
                    );

                    // Transférer le solde
                    if ($fleet->wallet_balance > 0) {
                        $user->increment('wallet_balance', $fleet->wallet_balance);
                    }
                } else {
                    $this->line("  [SIMUL] Création du profil Partner pour flotte {$fleet->name}");
                    $partner = new Partner(['id' => 8888 + $fleet->id]);
                }

                // Rattacher les chauffeurs et créer les affiliations
                $providers = Provider::where('fleet', $fleet->id)->get();
                foreach ($providers as $provider) {
                    if (!$simul) {
                        $provider->update(['partner_id' => $partner->id]);
                        PartnerAffiliate::firstOrCreate([
                            'partner_id' => $partner->id,
                            'affiliate_id' => $provider->id,
                            'affiliate_type' => 'PROVIDER'
                        ], [
                            'status' => 'ACTIVE'
                        ]);
                    } else {
                        $this->line("  [SIMUL] Rattachement du Chauffeur #{$provider->id} au partenaire Flotte");
                    }
                }

                // Migrer l'historique des transactions
                if (Schema::hasTable('fleet_wallets')) {
                    $transactions = DB::table('fleet_wallets')->where('fleet_id', $fleet->id)->get();
                    foreach ($transactions as $trans) {
                        if (!$simul) {
                            WalletPassbook::firstOrCreate([
                                'user_id' => $user->id,
                                'partner_id' => $partner->id,
                                'amount' => $trans->amount,
                                'via' => 'FLEET_TRIP_SHARE',
                                'created_at' => $trans->created_at
                            ], [
                                'status' => $trans->type === 'CREDIT' ? 'CREDITED' : 'DEBITED',
                                'description' => $trans->transaction_desc,
                                'updated_at' => $trans->updated_at
                            ]);
                        }
                    }
                }
            }

            // ==========================================
            // 2. MIGRATION DES STATION AGENTS
            // ==========================================
            $this->info("Migration des Station Agents...");
            $agents = StationAgent::all();

            foreach ($agents as $agent) {
                $this->line("Traitement de l'Agent #{$agent->id} - {$agent->name}...");

                $user = User::find($agent->user_id);
                if (!$user) {
                    $this->error("  Utilisateur ID {$agent->user_id} introuvable pour l'agent {$agent->name}. Ignoré.");
                    continue;
                }

                if (!$simul) {
                    $user->update(['user_type' => 'PARTNER']);
                }

                // Créer le profil Partner
                $partner = null;
                if (!$simul) {
                    $partner = Partner::firstOrCreate(
                        ['user_id' => $user->id, 'type' => 'STATION_AGENT'],
                        [
                            'partner_code' => $agent->agent_code ?: 'PRT-A' . str_pad($agent->id, 5, '0', STR_PAD_LEFT),
                            'status' => $agent->is_active ? 'APPROVED' : 'SUSPENDED',
                            'tier' => 'STANDARD',
                            'company_name' => $agent->name,
                            'pdp_stop_id' => $agent->pdp_stop_id,
                            'interurban_company_id' => $agent->interurban_company_id,
                            'commission_rules' => [
                                'passenger_scan_cfa' => $agent->commission_per_passenger,
                                'parcel_cfa' => $agent->commission_per_parcel
                            ]
                        ]
                    );

                    // Mettre à jour station_agent_id sur user
                    $user->update(['station_agent_id' => $agent->id]);

                    // Transférer le solde
                    if ($agent->wallet_balance > 0) {
                        $user->increment('wallet_balance', $agent->wallet_balance);
                    }
                } else {
                    $this->line("  [SIMUL] Création du profil Partner pour agent {$agent->name}");
                    $partner = new Partner(['id' => 7777 + $agent->id]);
                }

                // Migrer l'historique des transactions
                if (Schema::hasTable('agent_commission_logs')) {
                    $logs = DB::table('agent_commission_logs')->where('station_agent_id', $agent->id)->get();
                    foreach ($logs as $log) {
                        if (!$simul) {
                            $via = 'AGENT_PARCEL_COMMISSION';
                            if ($log->type === 'PASSENGER') {
                                $via = 'AGENT_PASSENGER_SCAN';
                            } elseif ($log->type === 'WITHDRAWAL') {
                                $via = 'WITHDRAWAL';
                            }

                            WalletPassbook::firstOrCreate([
                                'user_id' => $user->id,
                                'partner_id' => $partner->id,
                                'amount' => abs($log->amount),
                                'via' => $via,
                                'created_at' => $log->created_at
                            ], [
                                'status' => $log->amount > 0 ? 'CREDITED' : 'DEBITED',
                                'description' => $log->description,
                                'updated_at' => $log->updated_at
                            ]);
                        }
                    }
                }
            }

            if (!$simul) {
                DB::commit();
                $this->info("Migration et synchronisation terminées avec succès !");
            } else {
                DB::rollBack();
                $this->info("Simulation terminée. Aucune modification n'a été enregistrée.");
            }

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Erreur durant la migration : " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
