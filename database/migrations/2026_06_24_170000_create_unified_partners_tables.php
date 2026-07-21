<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnifiedPartnersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Create partners table
        if (!Schema::hasTable('partners')) {
            Schema::create('partners', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id');
                $table->string('partner_code', 20)->unique();
                $table->enum('type', ['FLEET_OWNER', 'SYNDICATE', 'STATION_AGENT', 'RECRUITER', 'AMBASSADOR', 'SPONSOR']);
                $table->enum('status', ['PENDING', 'ACTIVE', 'APPROVED', 'SUSPENDED'])->default('ACTIVE');
                $table->enum('tier', ['STANDARD', 'CERTIFIED', 'PREMIUM', 'PERMANENT'])->default('STANDARD');
                $table->string('company_name')->nullable();
                $table->string('logo')->nullable();
                $table->unsignedBigInteger('pdp_stop_id')->nullable();
                $table->unsignedBigInteger('interurban_company_id')->nullable();
                $table->json('commission_rules')->nullable();
                $table->json('metadata')->nullable();  // Données supplémentaires flexibles
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('pdp_stop_id')->references('id')->on('pdp_stops')->onDelete('set null');
                $table->foreign('interurban_company_id')->references('id')->on('interurban_companies')->onDelete('set null');
            });
        }

        // 2. Create partner_affiliates table
        if (!Schema::hasTable('partner_affiliates')) {
            Schema::create('partner_affiliates', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('partner_id');
                // Colonne unifiée : peut pointer vers un User ou un Provider
                $table->unsignedBigInteger('affiliated_user_id')->nullable();
                $table->unsignedBigInteger('affiliated_provider_id')->nullable();
                $table->enum('affiliated_type', ['USER', 'PROVIDER'])->default('USER');
                $table->enum('status', ['ACTIVE', 'SUSPENDED'])->default('ACTIVE');
                $table->decimal('commission_earned', 12, 2)->default(0);
                $table->timestamps();

                $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');
                $table->foreign('affiliated_user_id')->references('id')->on('users')->onDelete('set null');
                $table->unique(['partner_id', 'affiliated_user_id'], 'partner_user_affiliate_unique');
            });
        }

        // 3. Alter users table
        // Only ALTER the ENUM if PARTNER is not already a valid value.
        // On a fresh DB the original migration already defines the full ENUM,
        // so we skip the ALTER entirely (avoids the MySQL table-lock).
        if (Schema::hasColumn('users', 'user_type')) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $colType = \DB::selectOne(
                    "SELECT COLUMN_TYPE FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = 'users'
                        AND COLUMN_NAME = 'user_type'"
                );
                $typeStr = $colType ? $colType->COLUMN_TYPE : '';
                if (strpos($typeStr, 'PARTNER') === false) {
                    DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('USER','FLEET_OWNER','STATION_AGENT','DUAL','PARTNER') DEFAULT 'USER'");
                }
            } elseif (DB::connection()->getDriverName() === 'pgsql') {
                DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_user_type_check");
                DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check CHECK (user_type IN ('USER', 'FLEET_OWNER', 'STATION_AGENT', 'DUAL', 'PARTNER'))");
            }
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'referred_by_type')) {
                $col = $table->enum('referred_by_type', ['USER', 'PARTNER'])->default('USER');
                if (Schema::hasColumn('users', 'referred_by_id')) {
                    $col->after('referred_by_id');
                }
            }
        });

        // 4. Alter providers table
        Schema::table('providers', function (Blueprint $table) {
            if (!Schema::hasColumn('providers', 'partner_id')) {
                $col = $table->unsignedBigInteger('partner_id')->nullable();
                if (Schema::hasColumn('providers', 'fleet')) {
                    $col->after('fleet');
                }
                $table->foreign('partner_id')->references('id')->on('partners')->onDelete('set null');
            }

            if (!Schema::hasColumn('providers', 'referred_by_type')) {
                $col = $table->enum('referred_by_type', ['USER', 'PARTNER'])->default('USER');
                if (Schema::hasColumn('providers', 'referred_by_id')) {
                    $col->after('referred_by_id');
                }
            }
        });

        // 5. Alter wallet_passbooks table
        Schema::table('wallet_passbooks', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_passbooks', 'partner_id')) {
                $table->unsignedBigInteger('partner_id')->nullable()->after('user_id');
                $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');
            }
        });

        // 6. Alter withdrawals table (ajouter les colonnes du nouveau système)
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('withdrawals', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('withdrawals', 'partner_id')) {
                $table->unsignedBigInteger('partner_id')->nullable()->after('user_id');
                $table->foreign('partner_id')->references('id')->on('partners')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            if (Schema::hasColumn('withdrawals', 'partner_id')) {
                $table->dropForeign(['partner_id']);
                $table->dropColumn('partner_id');
            }
            if (Schema::hasColumn('withdrawals', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        Schema::table('wallet_passbooks', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->dropColumn('partner_id');
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->dropColumn(['partner_id', 'referred_by_type']);
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'user_type')) {
                if (DB::connection()->getDriverName() === 'mysql') {
                    DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('USER', 'FLEET_OWNER', 'STATION_AGENT', 'DUAL') DEFAULT 'USER'");
                } elseif (DB::connection()->getDriverName() === 'pgsql') {
                    DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_user_type_check");
                    DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check CHECK (user_type IN ('USER', 'FLEET_OWNER', 'STATION_AGENT', 'DUAL'))");
                }
            }
            $table->dropColumn('referred_by_type');
        });

        Schema::dropIfExists('partner_affiliates');
        Schema::dropIfExists('partners');
    }
}
