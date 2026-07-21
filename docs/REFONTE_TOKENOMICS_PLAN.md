# 🏗️ REFONTE PICMEE225 - TOKENOMICS & MULTIMODAL

**Date**: 23 Novembre 2025  
**Lead Architect**: Laravel Expert  
**Mission**: Implémentation complète Staking Dynamique + Feeder Rides  

---

## 📋 PHASE 0: NETTOYAGE (SÉCURITÉ)

### Fichiers Parasites Détectés: 28 fichiers

#### Commande de Nettoyage PowerShell:
```powershell
# Supprimer tous les fichiers .save
Get-ChildItem -Path "c:\Users\HP\Desktop\zip\generated_output\picme225.com" -Recurse -Filter "*.save" | Remove-Item -Force

# Supprimer tous les fichiers .phpold
Get-ChildItem -Path "c:\Users\HP\Desktop\zip\generated_output\picme225.com" -Recurse -Filter "*.phpold" | Remove-Item -Force

# Supprimer tous les fichiers .json5
Get-ChildItem -Path "c:\Users\HP\Desktop\zip\generated_output\picme225.com" -Recurse -Filter "*.json5" | Remove-Item -Force

# Vérification
Write-Host "Nettoyage terminé. Fichiers supprimés:" -ForegroundColor Green
Get-ChildItem -Path "c:\Users\HP\Desktop\zip\generated_output\picme225.com" -Recurse | Where-Object { $_.Extension -in '.save','.phpold','.json5' } | Measure-Object | Select-Object -ExpandProperty Count
```

#### Fichiers à Supprimer:
```
✗ 23 fichiers .save
✗ 4 fichiers .phpold
✗ 1 fichier .json5
━━━━━━━━━━━━━━━━━━━━━━
Total: 28 fichiers parasites
```

---

## 📊 RÈGLES MÉTIER - RÉCAPITULATIF

### 1. Staking Dynamique
```
Règle: Solde_ECO_Driver >= Commission_Course
Si insuffisant → Course NON attribuée
Tri: Par solde ECO DESC, puis distance ASC
```

### 2. Bonus Lancement
```
Conditions:
- 1000 premiers chauffeurs PAR ServiceType
- Montant: 100 ECO
- Durée: 90 jours (date à date)
- Expiration: Retrait automatique après 90j
```

### 3. Feeder Rides (Approche Intelligente)
```
Trigger: 
- Course INTERCITY + requires_feeder_ride = true
- Distance gare > feeder_trigger_radius

Action:
- Chercher ServiceTypes avec can_act_as_feeder = true
- Proposer taxi d'approche (Woro, Taxi, etc.)
```

---

## 🗄️ PHASE 1: MIGRATIONS

### Migration 1: service_types (Tokenomics + Feeder)
**Fichier**: `2025_11_24_000001_add_tokenomics_and_feeder_to_service_types.php`

```php
Schema::table('service_types', function (Blueprint $table) {
    // INTERCITY & FEEDER
    $table->boolean('is_intercity')->default(false)
          ->after('sharing_type')
          ->comment('Service gare à gare (Car, Bus)');
    
    $table->boolean('requires_feeder_ride')->default(false)
          ->after('is_intercity')
          ->comment('Nécessite un taxi d\'approche');
    
    $table->boolean('can_act_as_feeder')->default(false)
          ->after('requires_feeder_ride')
          ->comment('Peut servir de taxi d\'approche (Woro, Taxi)');
    
    $table->integer('feeder_trigger_radius')->nullable()
          ->after('can_act_as_feeder')
          ->comment('Distance (km) déclenchant l\'approche');
    
    // TOKENOMICS
    $table->decimal('commission_percentage', 5, 2)->default(10.00)
          ->after('feeder_trigger_radius')
          ->comment('Commission en % (ex: 10.00 = 10%)');
    
    $table->decimal('eco_discount_percent', 5, 2)->default(5.00)
          ->after('commission_percentage')
          ->comment('Réduction si paiement ECO (ex: 5.00 = 5%)');
    
    // Index
    $table->index('is_intercity');
    $table->index('can_act_as_feeder');
});
```

### Migration 2: providers (Wallet ECO + Bonus)
**Fichier**: `2025_11_24_000002_add_eco_wallet_to_providers.php`

```php
Schema::table('providers', function (Blueprint $table) {
    // WALLET ECO
    $table->decimal('eco_wallet_balance', 15, 4)->default(0.0000)
          ->after('wallet_balance')
          ->comment('Solde EcoToken du chauffeur');
    
    // BONUS LANCEMENT
    $table->timestamp('bonus_expires_at')->nullable()
          ->after('eco_wallet_balance')
          ->comment('Date d\'expiration du bonus 100 ECO');
    
    $table->boolean('bonus_received')->default(false)
          ->after('bonus_expires_at')
          ->comment('A reçu le bonus de lancement');
    
    // Index pour performance
    $table->index('eco_wallet_balance');
    $table->index('bonus_expires_at');
});
```

### Migration 3: users (VIP + Overdraft + Payment)
**Fichier**: `2025_11_24_000003_add_vip_and_payment_to_users.php`

```php
Schema::table('users', function (Blueprint $table) {
    // VIP STATUS
    $table->boolean('is_vip')->default(false)
          ->after('wallet_balance')
          ->comment('Utilisateur VIP');
    
    $table->timestamp('vip_expires_at')->nullable()
          ->after('is_vip')
          ->comment('Date d\'expiration VIP');
    
    // OVERDRAFT (Découvert autorisé)
    $table->decimal('overdraft_limit', 10, 2)->default(0.00)
          ->after('vip_expires_at')
          ->comment('Limite de découvert autorisée');
    
    // PAYMENT MODE
    $table->enum('preferred_payment_mode', [
        'CASH', 'CARD', 'WALLET', 'ECO_TOKEN', 'MOBILE_MONEY'
    ])->default('CASH')
      ->after('overdraft_limit')
      ->comment('Mode de paiement préféré');
    
    // Index
    $table->index('is_vip');
    $table->index('preferred_payment_mode');
});
```

### Migration 4: user_requests (Feeder + Chain + QR)
**Fichier**: `2025_11_24_000004_add_feeder_chain_to_user_requests.php`

```php
Schema::table('user_requests', function (Blueprint $table) {
    // FEEDER RIDE
    $table->boolean('is_feeder_ride')->default(false)
          ->after('status')
          ->comment('Est un trajet d\'approche');
    
    $table->uuid('chain_uuid')->nullable()
          ->after('is_feeder_ride')
          ->comment('UUID liant feeder + main ride');
    
    $table->unsignedBigInteger('main_ride_id')->nullable()
          ->after('chain_uuid')
          ->comment('ID du trajet principal (si feeder)');
    
    // QR CODE TICKET
    $table->string('qr_code_ticket', 255)->nullable()
          ->after('main_ride_id')
          ->comment('QR Code unique pour validation');
    
    $table->timestamp('qr_scanned_at')->nullable()
          ->after('qr_code_ticket')
          ->comment('Date de scan du QR');
    
    // PAYMENT MODE USED
    $table->enum('payment_mode_used', [
        'CASH', 'CARD', 'WALLET', 'ECO_TOKEN', 'MOBILE_MONEY'
    ])->nullable()
      ->after('qr_scanned_at')
      ->comment('Mode de paiement utilisé');
    
    // Index
    $table->index('is_feeder_ride');
    $table->index('chain_uuid');
    $table->index('main_ride_id');
    $table->index('qr_code_ticket');
    
    // Foreign key
    $table->foreign('main_ride_id')
          ->references('id')
          ->on('user_requests')
          ->onDelete('set null');
});
```

### Migration 5: eco_transactions (Historique ECO)
**Fichier**: `2025_11_24_000005_create_eco_transactions_table.php`

```php
Schema::create('eco_transactions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('provider_id');
    $table->enum('type', [
        'BONUS_LAUNCH',      // Bonus 100 ECO
        'BONUS_EXPIRY',      // Retrait bonus expiré
        'COMMISSION_DEBIT',  // Paiement commission
        'RIDE_EARNING',      // Gain de course
        'TRANSFER_IN',       // Transfert entrant
        'TRANSFER_OUT',      // Transfert sortant
        'ADMIN_ADJUSTMENT',  // Ajustement admin
    ]);
    $table->decimal('amount', 15, 4);
    $table->decimal('balance_before', 15, 4);
    $table->decimal('balance_after', 15, 4);
    $table->unsignedBigInteger('ride_id')->nullable();
    $table->text('description')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
    
    // Foreign keys
    $table->foreign('provider_id')
          ->references('id')
          ->on('providers')
          ->onDelete('cascade');
    
    $table->foreign('ride_id')
          ->references('id')
          ->on('user_requests')
          ->onDelete('set null');
    
    // Index
    $table->index(['provider_id', 'created_at']);
    $table->index('type');
});
```

---

## 🎯 PHASE 2: LOGIQUE MÉTIER (MODELS)

### 1. ServiceType.php - Scopes Feeder

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    protected $fillable = [
        // ... champs existants ...
        'is_intercity',
        'requires_feeder_ride',
        'can_act_as_feeder',
        'feeder_trigger_radius',
        'commission_percentage',
        'eco_discount_percent',
    ];

    protected $casts = [
        // ... casts existants ...
        'is_intercity' => 'boolean',
        'requires_feeder_ride' => 'boolean',
        'can_act_as_feeder' => 'boolean',
        'feeder_trigger_radius' => 'integer',
        'commission_percentage' => 'decimal:2',
        'eco_discount_percent' => 'decimal:2',
    ];

    /**
     * Scope: Services pouvant servir de feeder (approche)
     */
    public function scopeFeeders($query)
    {
        return $query->where('can_act_as_feeder', true)
                     ->where('status', 1);
    }

    /**
     * Scope: Services intercity (gare à gare)
     */
    public function scopeIntercity($query)
    {
        return $query->where('is_intercity', true);
    }

    /**
     * Calculer la commission pour un montant donné
     */
    public function calculateCommission($amount)
    {
        return ($amount * $this->commission_percentage) / 100;
    }

    /**
     * Calculer la réduction ECO
     */
    public function calculateEcoDiscount($amount)
    {
        return ($amount * $this->eco_discount_percent) / 100;
    }
}
```

### 2. Provider.php - Solvabilité ECO

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = [
        // ... champs existants ...
        'eco_wallet_balance',
        'bonus_expires_at',
        'bonus_received',
    ];

    protected $casts = [
        // ... casts existants ...
        'eco_wallet_balance' => 'decimal:4',
        'bonus_expires_at' => 'datetime',
        'bonus_received' => 'boolean',
    ];

    /**
     * Vérifier si le chauffeur peut payer la commission
     */
    public function canAffordCommission($commissionAmount)
    {
        return $this->eco_wallet_balance >= $commissionAmount;
    }

    /**
     * Débiter la commission ECO
     */
    public function debitCommission($commissionAmount, $rideId, $description = null)
    {
        if (!$this->canAffordCommission($commissionAmount)) {
            throw new \Exception('Solde ECO insuffisant pour payer la commission');
        }

        $balanceBefore = $this->eco_wallet_balance;
        $this->eco_wallet_balance -= $commissionAmount;
        $this->save();

        // Enregistrer la transaction
        EcoTransaction::create([
            'provider_id' => $this->id,
            'type' => 'COMMISSION_DEBIT',
            'amount' => -$commissionAmount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->eco_wallet_balance,
            'ride_id' => $rideId,
            'description' => $description ?? 'Commission course #' . $rideId,
        ]);

        return true;
    }

    /**
     * Créditer le wallet ECO
     */
    public function creditEco($amount, $type, $description = null, $metadata = [])
    {
        $balanceBefore = $this->eco_wallet_balance;
        $this->eco_wallet_balance += $amount;
        $this->save();

        // Enregistrer la transaction
        EcoTransaction::create([
            'provider_id' => $this->id,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->eco_wallet_balance,
            'description' => $description,
            'metadata' => $metadata,
        ]);

        return true;
    }

    /**
     * Vérifier si le bonus a expiré
     */
    public function hasBonusExpired()
    {
        return $this->bonus_expires_at && 
               $this->bonus_expires_at->isPast() && 
               $this->bonus_received;
    }

    /**
     * Relation: Transactions ECO
     */
    public function ecoTransactions()
    {
        return $this->hasMany(EcoTransaction::class, 'provider_id');
    }
}
```

### 3. EcoTransaction.php (Nouveau Modèle)

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EcoTransaction extends Model
{
    protected $fillable = [
        'provider_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'ride_id',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'metadata' => 'array',
    ];

    /**
     * Relation: Provider
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Relation: Ride
     */
    public function ride()
    {
        return $this->belongsTo(UserRequests::class, 'ride_id');
    }
}
```

### 4. ProviderObserver.php (Bonus Automatique)

```php
<?php

namespace App\Observers;

use App\Provider;
use App\ServiceType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProviderObserver
{
    /**
     * Handle the Provider "created" event.
     * Attribution du bonus de lancement (100 ECO)
     */
    public function created(Provider $provider)
    {
        try {
            // Récupérer le ServiceType du provider
            $serviceTypeId = $provider->service_type ?? null;
            
            if (!$serviceTypeId) {
                return;
            }

            // Compter les providers existants pour ce ServiceType
            $count = Provider::where('service_type', $serviceTypeId)
                            ->where('bonus_received', true)
                            ->count();

            // Si moins de 1000, donner le bonus
            if ($count < 1000) {
                $provider->creditEco(
                    100.00,
                    'BONUS_LAUNCH',
                    'Bonus de lancement - 1000 premiers chauffeurs',
                    ['service_type_id' => $serviceTypeId, 'rank' => $count + 1]
                );

                $provider->bonus_received = true;
                $provider->bonus_expires_at = Carbon::now()->addDays(90);
                $provider->save();

                Log::info("Bonus lancement attribué au provider #{$provider->id} (Rang: " . ($count + 1) . ")");
            }
        } catch (\Exception $e) {
            Log::error("Erreur attribution bonus provider #{$provider->id}: " . $e->getMessage());
        }
    }
}
```

---

## 🎯 PHASE 3: DISPATCHER (CŒUR DU SYSTÈME)

### DispatcherController.php - Logique Staking + Feeder

```php
<?php

namespace App\Http\Controllers;

use App\Provider;
use App\ServiceType;
use App\UserRequests;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DispatcherController extends Controller
{
    /**
     * Dispatcher principal avec Staking Dynamique
     */
    public function dispatchRide(UserRequests $request)
    {
        $serviceType = ServiceType::find($request->service_type_id);
        
        if (!$serviceType) {
            return $this->error('ServiceType introuvable');
        }

        // Calculer la commission exacte
        $ridePrice = $request->estimated_fare ?? 0;
        $commission = $serviceType->calculateCommission($ridePrice);

        // FILTRE 1: Solvabilité ECO (RÈGLE STRICTE)
        $eligibleProviders = Provider::where('service_type', $serviceType->id)
            ->where('status', 'approved')
            ->where('is_available', 1)
            ->where('eco_wallet_balance', '>=', $commission) // ⭐ STAKING DYNAMIQUE
            ->get();

        if ($eligibleProviders->isEmpty()) {
            Log::warning("Aucun chauffeur solvable pour la course #{$request->id} (Commission: {$commission} ECO)");
            return $this->error('Aucun chauffeur disponible avec solde ECO suffisant');
        }

        // FILTRE 2: Distance géographique
        $nearbyProviders = $this->filterByDistance(
            $eligibleProviders,
            $request->s_latitude,
            $request->s_longitude,
            10 // rayon en km
        );

        // TRI: Par solde ECO DESC, puis distance ASC
        $sortedProviders = $nearbyProviders->sortByDesc(function($provider) use ($request) {
            // Priorité 1: Solde ECO (plus riche = prioritaire)
            $ecoScore = $provider->eco_wallet_balance * 1000;
            
            // Priorité 2: Distance (plus proche = bonus)
            $distance = $this->calculateDistance(
                $request->s_latitude,
                $request->s_longitude,
                $provider->latitude,
                $provider->longitude
            );
            $distanceScore = max(0, 100 - $distance);
            
            return $ecoScore + $distanceScore;
        });

        // Notifier les chauffeurs dans l'ordre
        foreach ($sortedProviders->take(5) as $provider) {
            $this->notifyProvider($provider, $request);
        }

        return $this->success('Course dispatchée', [
            'providers_notified' => $sortedProviders->take(5)->pluck('id'),
            'commission_required' => $commission,
        ]);
    }

    /**
     * Vérifier si un feeder ride est nécessaire
     */
    public function checkFeederRequired(UserRequests $request)
    {
        $serviceType = ServiceType::find($request->service_type_id);

        if (!$serviceType || !$serviceType->is_intercity || !$serviceType->requires_feeder_ride) {
            return false;
        }

        // Calculer la distance jusqu'à la gare
        $stationDistance = $this->calculateDistanceToNearestStation(
            $request->s_latitude,
            $request->s_longitude
        );

        // Si distance > radius, feeder nécessaire
        return $stationDistance > ($serviceType->feeder_trigger_radius ?? 5);
    }

    /**
     * Créer un feeder ride automatiquement
     */
    public function createFeederRide(UserRequests $mainRide)
    {
        $chainUuid = Str::uuid();

        // Trouver les ServiceTypes éligibles comme feeder
        $feederServiceTypes = ServiceType::feeders()->get();

        if ($feederServiceTypes->isEmpty()) {
            Log::warning("Aucun ServiceType feeder disponible");
            return null;
        }

        // Créer le feeder ride
        $feederRide = UserRequests::create([
            'booking_id' => 'FEEDER-' . time(),
            'user_id' => $mainRide->user_id,
            'service_type_id' => $feederServiceTypes->first()->id,
            's_latitude' => $mainRide->s_latitude,
            's_longitude' => $mainRide->s_longitude,
            'd_latitude' => $this->getNearestStationCoords($mainRide->s_latitude, $mainRide->s_longitude)['lat'],
            'd_longitude' => $this->getNearestStationCoords($mainRide->s_latitude, $mainRide->s_longitude)['lng'],
            'is_feeder_ride' => true,
            'chain_uuid' => $chainUuid,
            'main_ride_id' => $mainRide->id,
            'status' => 'SEARCHING',
        ]);

        // Mettre à jour le main ride
        $mainRide->update(['chain_uuid' => $chainUuid]);

        // Dispatcher le feeder
        $this->dispatchRide($feederRide);

        return $feederRide;
    }

    /**
     * Calculer la distance entre deux points
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Filtrer les providers par distance
     */
    private function filterByDistance($providers, $lat, $lon, $maxDistance)
    {
        return $providers->filter(function($provider) use ($lat, $lon, $maxDistance) {
            $distance = $this->calculateDistance(
                $lat, $lon,
                $provider->latitude,
                $provider->longitude
            );
            return $distance <= $maxDistance;
        });
    }
}
```

---

## ⏰ PHASE 4: AUTOMATISATION (CRON JOBS)

### Command: ExpireBonuses.php

```php
<?php

namespace App\Console\Commands;

use App\Provider;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireBonuses extends Command
{
    protected $signature = 'eco:expire-bonuses';
    protected $description = 'Retire les bonus ECO expirés (90 jours)';

    public function handle()
    {
        $this->info('🔍 Recherche des bonus expirés...');

        $expiredProviders = Provider::where('bonus_received', true)
            ->where('bonus_expires_at', '<=', Carbon::now())
            ->where('eco_wallet_balance', '>=', 100)
            ->get();

        $count = 0;

        foreach ($expiredProviders as $provider) {
            try {
                $balanceBefore = $provider->eco_wallet_balance;
                
                // Débiter 100 ECO
                $provider->eco_wallet_balance -= 100;
                $provider->bonus_received = false;
                $provider->bonus_expires_at = null;
                $provider->save();

                // Enregistrer la transaction
                \App\EcoTransaction::create([
                    'provider_id' => $provider->id,
                    'type' => 'BONUS_EXPIRY',
                    'amount' => -100.00,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $provider->eco_wallet_balance,
                    'description' => 'Expiration bonus lancement (90 jours)',
                ]);

                $count++;
                $this->line("✅ Provider #{$provider->id}: Bonus expiré et retiré");
            } catch (\Exception $e) {
                $this->error("❌ Erreur provider #{$provider->id}: " . $e->getMessage());
                Log::error("Erreur expiration bonus provider #{$provider->id}: " . $e->getMessage());
            }
        }

        $this->info("✅ Terminé! {$count} bonus expirés traités.");
        
        return 0;
    }
}
```

### Kernel.php - Planification

```php
protected function schedule(Schedule $schedule)
{
    // Expirer les bonus tous les jours à 2h du matin
    $schedule->command('eco:expire-bonuses')
             ->dailyAt('02:00')
             ->withoutOverlapping()
             ->onOneServer();
}
```

---

## 📋 CHECKLIST D'IMPLÉMENTATION

### Phase 0: Nettoyage ✅
- [ ] Exécuter commande PowerShell
- [ ] Vérifier suppression (28 fichiers)

### Phase 1: Migrations
- [ ] Créer migration service_types (tokenomics + feeder)
- [ ] Créer migration providers (eco_wallet)
- [ ] Créer migration users (VIP + payment)
- [ ] Créer migration user_requests (feeder + chain)
- [ ] Créer migration eco_transactions
- [ ] Exécuter: `php artisan migrate`

### Phase 2: Models
- [ ] Mettre à jour ServiceType.php
- [ ] Mettre à jour Provider.php
- [ ] Créer EcoTransaction.php
- [ ] Créer ProviderObserver.php
- [ ] Enregistrer Observer dans AppServiceProvider

### Phase 3: Dispatcher
- [ ] Mettre à jour DispatcherController.php
- [ ] Implémenter dispatchRide() avec staking
- [ ] Implémenter checkFeederRequired()
- [ ] Implémenter createFeederRide()

### Phase 4: Automatisation
- [ ] Créer Command ExpireBonuses
- [ ] Planifier dans Kernel.php
- [ ] Tester: `php artisan eco:expire-bonuses`

### Tests
- [ ] Tester attribution bonus (1000 premiers)
- [ ] Tester expiration bonus (90j)
- [ ] Tester dispatch avec staking
- [ ] Tester feeder ride creation
- [ ] Tester commission debit

---

**Document généré le**: 23 Novembre 2025  
**Status**: 📋 **PLAN COMPLET PRÊT**  
**Prochaine étape**: Exécuter Phase 0 (Nettoyage)
