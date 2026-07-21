<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 5 — Feature Flags + Activation System
 * Phase 6 — Service Waitlist
 *
 * Creates:
 *  feature_flags    — admin-controlled toggles per service/zone
 *  service_waitlist — queue when fleet capacity is insufficient
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Phase 5: Feature Flags ────────────────────────────────────────
        if (!Schema::hasTable('feature_flags')) {
            Schema::create('feature_flags', function (Blueprint $table) {
                $table->id();

                // Flag identifier (e.g. 'delivery_enabled', 'subscription_required')
                $table->string('key')->unique();

                // Human-readable label for the admin panel
                $table->string('label');
                $table->text('description')->nullable();

                // Toggle
                $table->boolean('is_enabled')->default(false);

                // Optional scoping: restrict to a specific service or zone
                $table->unsignedBigInteger('service_id')->nullable();
                $table->string('zone')->nullable();   // e.g. 'ABIDJAN', 'BOUAKE', '*'

                // Activation conditions (JSON: { "min_active_providers": 5 })
                $table->json('activation_conditions')->nullable();

                // Metadata
                $table->string('category')->default('general');  // general|service|payment|ux
                $table->unsignedBigInteger('updated_by')->nullable();   // admin user id
                $table->timestamps();

                $table->index(['key', 'is_enabled']);
                $table->index('service_id');
            });
        }

        // ── Phase 5: Fleet Capacity Snapshots (for auto-activation) ───────
        if (!Schema::hasTable('fleet_capacity_snapshots')) {
            Schema::create('fleet_capacity_snapshots', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_id')->nullable();
                $table->string('zone')->default('*');

                // Counts at snapshot time
                $table->unsignedInteger('online_providers')->default(0);
                $table->unsignedInteger('active_requests')->default(0);
                $table->float('utilization_rate')->default(0); // active/online ratio 0-1
                $table->float('avg_wait_time_min')->nullable(); // avg pickup wait

                // Thresholds met
                $table->boolean('threshold_met')->default(false);

                $table->timestamp('snapped_at')->useCurrent();
                $table->timestamps();

                $table->index(['service_id', 'snapped_at']);
            });
        }

        // ── Phase 6: Service Waitlist ──────────────────────────────────────
        if (!Schema::hasTable('service_waitlist')) {
            Schema::create('service_waitlist', function (Blueprint $table) {
                $table->id();

                // Who is waiting
                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                // What service/type they want
                $table->unsignedBigInteger('service_id')->nullable();
                $table->unsignedBigInteger('service_type_id')->nullable();

                // Their location at time of joining
                $table->double('latitude', 15, 8)->nullable();
                $table->double('longitude', 15, 8)->nullable();
                $table->string('zone')->nullable();

                // Queue position (recalculated on joins/leaves)
                $table->unsignedInteger('position')->default(0);

                // Status: waiting | notified | converted | expired | cancelled
                $table->enum('status', ['waiting','notified','converted','expired','cancelled'])
                      ->default('waiting');

                // When they were notified (for TTL expiry)
                $table->timestamp('notified_at')->nullable();

                // Their subscription plan at time of joining (affects priority)
                $table->unsignedBigInteger('subscription_plan_id')->nullable();

                // Optional: preferred time slot
                $table->time('preferred_time')->nullable();

                $table->timestamps();

                $table->index(['service_id', 'status', 'position']);
                $table->index(['user_id', 'status']);
                $table->index('zone');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_waitlist');
        Schema::dropIfExists('fleet_capacity_snapshots');
        Schema::dropIfExists('feature_flags');
    }
};
