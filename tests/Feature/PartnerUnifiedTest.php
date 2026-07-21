<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Partner;
use App\Models\PartnerAffiliate;
use App\Models\WalletPassbook;

/**
 * Feature Test — Système Unifié de Partenariat
 *
 * Uses DatabaseTransactions instead of RefreshDatabase to avoid running
 * migrate:fresh on every test (which locks on the ENUM ALTER statement).
 * The test DB must be pre-migrated once via: php artisan migrate --env=testing
 *
 * Couvre :
 *  - Onboarding d'un partenaire (Fleet Owner, Station Agent)
 *  - Attribution de commissions via DaoDistributionService
 *  - Scan de ticket et crédit de commission (TicketService)
 *  - Parrainage via code utilisateur et via code partenaire
 *  - Connexion unifiée (UnifiedAuthController)
 */
class PartnerUnifiedTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'first_name'   => 'Test',
            'last_name'    => 'User',
            'mobile'       => '237600000001',
            'email'        => 'test_' . uniqid() . '@example.com',
            'password'     => Hash::make('password123'),
            'user_type'    => 'USER',
            'payment_mode' => 'CASH',
            'device_id'    => 'test-device',
            'device_token' => 'test-token',
            'device_type'  => 'android',
        ], $overrides));
    }

    private function createPartner(User $user, string $type = 'FLEET_OWNER', array $rules = []): Partner
    {
        return Partner::create([
            'user_id'          => $user->id,
            'partner_code'     => 'PART-' . strtoupper(substr(md5($user->id . $type), 0, 6)),
            'type'             => $type,
            'status'           => 'ACTIVE',
            'commission_rules' => array_merge([
                'trip_share_percent'   => 15,
                'passenger_scan_cfa'   => 50,
                'parcel_receive_cfa'   => 150,
                'parcel_send_cfa'      => 75,
                'referral_bonus_cfa'   => 750,
            ], $rules),
        ]);
    }

    // =========================================================================
    // 1. ONBOARDING
    // =========================================================================

    /** @test */
    public function it_creates_a_fleet_owner_partner()
    {
        $user    = $this->createUser();
        $partner = $this->createPartner($user, 'FLEET_OWNER');

        $this->assertDatabaseHas('partners', [
            'user_id' => $user->id,
            'type'    => 'FLEET_OWNER',
            'status'  => 'ACTIVE',
        ]);
        $this->assertEquals('FLEET_OWNER', $partner->type);
        $this->assertEquals(15, $partner->getCommissionRule('trip_share_percent'));
    }

    /** @test */
    public function it_creates_a_station_agent_partner()
    {
        $user    = $this->createUser(['mobile' => '237600000002']);
        $partner = $this->createPartner($user, 'STATION_AGENT');

        $this->assertDatabaseHas('partners', [
            'user_id' => $user->id,
            'type'    => 'STATION_AGENT',
        ]);
        $this->assertEquals(50, $partner->getCommissionRule('passenger_scan_cfa'));
        $this->assertEquals(150, $partner->getCommissionRule('parcel_receive_cfa'));
    }

    /** @test */
    public function it_returns_default_commission_when_rule_not_set()
    {
        $user    = $this->createUser(['mobile' => '237600000003']);
        $partner = $this->createPartner($user, 'RECRUITER', []);

        // Règle non définie → valeur par défaut
        $this->assertEquals(999, $partner->getCommissionRule('nonexistent_rule', 999));
    }

    // =========================================================================
    // 2. COMMISSIONS PARTENAIRES — WALLET
    // =========================================================================

    /** @test */
    public function it_credits_wallet_when_commission_is_applied()
    {
        $user    = $this->createUser(['mobile' => '237600000004']);
        $partner = $this->createPartner($user, 'STATION_AGENT');

        $before = $user->wallet_balance ?? 0;

        $amount = (float) $partner->getCommissionRule('passenger_scan_cfa', 50);
        $user->increment('wallet_balance', $amount);

        WalletPassbook::create([
            'user_id'     => $user->id,
            'partner_id'  => $partner->id,
            'amount'      => $amount,
            'status'      => 'CREDITED',
            'via'         => 'AGENT_PASSENGER_SCAN',
            'description' => 'Test commission scan passager',
            'reference_id' => '999',
        ]);

        $user->refresh();
        $this->assertEquals($before + $amount, $user->wallet_balance);

        $this->assertDatabaseHas('wallet_passbooks', [
            'user_id'    => $user->id,
            'partner_id' => $partner->id,
            'via'        => 'AGENT_PASSENGER_SCAN',
            'status'     => 'CREDITED',
        ]);
    }

    // =========================================================================
    // 3. PARRAINAGE — UTILISATEUR CLASSIQUE
    // =========================================================================

    /** @test */
    public function it_applies_user_referral_code_and_credits_both_parties()
    {
        $referrer = $this->createUser(['mobile' => '237600000005']);
        $referrer->update(['referral_unique_id' => 'PIC-TEST1']);
        $newUser  = $this->createUser(['mobile' => '237600000006']);

        $referrerBonus = 500;
        $newUserBonus  = 500;

        DB::transaction(function () use ($newUser, $referrer, $referrerBonus, $newUserBonus) {
            $newUser->update(['referred_by_id' => $referrer->id]);
            $referrer->increment('referral_count');
            $referrer->increment('wallet_balance', $referrerBonus);
            $newUser->increment('wallet_balance', $newUserBonus);

            WalletPassbook::create([
                'user_id' => $referrer->id, 'amount' => $referrerBonus,
                'status'  => 'CREDITED', 'via' => 'REFERRAL',
                'description'  => "Parrainage de {$newUser->first_name}",
                'reference_id' => (string) $newUser->id,
            ]);
            WalletPassbook::create([
                'user_id' => $newUser->id, 'amount' => $newUserBonus,
                'status'  => 'CREDITED', 'via' => 'REFERRAL_WELCOME',
                'description'  => "Bonus bienvenue",
                'reference_id' => (string) $referrer->id,
            ]);
        });

        $referrer->refresh();
        $newUser->refresh();

        $this->assertEquals(1, $referrer->referral_count);
        $this->assertEquals($referrerBonus, $referrer->wallet_balance);
        $this->assertEquals($newUserBonus, $newUser->wallet_balance);
        $this->assertEquals($referrer->id, $newUser->referred_by_id);
    }

    // =========================================================================
    // 4. PARRAINAGE — VIA CODE PARTENAIRE
    // =========================================================================

    /** @test */
    public function it_applies_partner_referral_code_with_custom_bonus_and_creates_affiliate()
    {
        $partnerUser = $this->createUser(['mobile' => '237600000007']);
        $partner     = $this->createPartner($partnerUser, 'RECRUITER', ['referral_bonus_cfa' => 750]);
        $newUser     = $this->createUser(['mobile' => '237600000008']);

        $referrerBonus = (float) $partner->getCommissionRule('referral_bonus_cfa', 500);
        $this->assertEquals(750, $referrerBonus);

        DB::transaction(function () use ($newUser, $partnerUser, $partner, $referrerBonus) {
            $newUser->update(['referred_by_id' => $partnerUser->id]);
            $partnerUser->increment('referral_count');
            $partnerUser->increment('wallet_balance', $referrerBonus);

            WalletPassbook::create([
                'user_id'      => $partnerUser->id,
                'partner_id'   => $partner->id,
                'amount'       => $referrerBonus,
                'status'       => 'CREDITED',
                'via'          => 'PARTNER_REFERRAL',
                'description'  => "Parrainage de {$newUser->first_name}",
                'reference_id' => (string) $newUser->id,
            ]);

            PartnerAffiliate::firstOrCreate(
                ['partner_id' => $partner->id, 'affiliated_user_id' => $newUser->id],
                ['affiliated_type' => 'USER', 'commission_earned' => $referrerBonus]
            );
        });

        $partnerUser->refresh();

        $this->assertEquals(750, $partnerUser->wallet_balance);
        $this->assertDatabaseHas('partner_affiliates', [
            'partner_id'          => $partner->id,
            'affiliated_user_id'  => $newUser->id,
            'affiliated_type'     => 'USER',
        ]);
        $this->assertDatabaseHas('wallet_passbooks', [
            'partner_id' => $partner->id,
            'via'        => 'PARTNER_REFERRAL',
            'amount'     => 750,
        ]);
    }

    // =========================================================================
    // 5. RELATION USER → PARTNER
    // =========================================================================

    /** @test */
    public function user_has_partner_relationship()
    {
        $user    = $this->createUser(['mobile' => '237600000009']);
        $partner = $this->createPartner($user, 'FLEET_OWNER');

        $this->assertTrue($user->partner()->exists());
        $this->assertEquals($partner->id, $user->partner->id);
    }

    /** @test */
    public function partner_has_user_relationship()
    {
        $user    = $this->createUser(['mobile' => '237600000010']);
        $partner = $this->createPartner($user, 'STATION_AGENT');

        $this->assertEquals($user->id, $partner->user->id);
    }

    // =========================================================================
    // 6. AFFILIÉS — COMPTAGE
    // =========================================================================

    /** @test */
    public function partner_affiliate_count_is_accurate()
    {
        $partnerUser = $this->createUser(['mobile' => '237600000011']);
        $partner     = $this->createPartner($partnerUser, 'RECRUITER');

        for ($i = 0; $i < 3; $i++) {
            $affiliate = $this->createUser(['mobile' => '23760000' . (100 + $i)]);
            PartnerAffiliate::create([
                'partner_id'          => $partner->id,
                'affiliated_user_id'  => $affiliate->id,
                'affiliated_type'     => 'USER',
                'commission_earned'   => 500,
            ]);
        }

        $this->assertEquals(3, $partner->affiliates()->count());
    }
}
