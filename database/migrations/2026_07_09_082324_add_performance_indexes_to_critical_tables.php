<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Performance Indexes - PostgreSQL + MySQL Compatible
 * 
 * This migration adds performance indexes to the most queried columns.
 * Each index is wrapped in a try/catch to allow idempotent re-runs
 * (useful if some indexes already exist on production).
 */
class AddPerformanceIndexesToCriticalTables extends Migration
{
    /**
     * Disable transaction for Postgres so that a caught exception doesn't abort the entire migration batch.
     */
    public $withinTransaction = false;
    /**
     * List of indexes to create.
     * Format: [table, columns[], index_name]
     */
    private $indexes = [
        // user_requests - most queried table
        ['user_requests', ['provider_id'],         'idx_user_requests_provider_id'],
        ['user_requests', ['user_id'],              'idx_user_requests_user_id'],
        ['user_requests', ['status'],               'idx_user_requests_status'],
        ['user_requests', ['provider_id', 'status'],'idx_user_requests_provider_status'],
        ['user_requests', ['created_at'],           'idx_user_requests_created_at'],

        // request_filters - dispatching polling
        ['request_filters', ['request_id'],        'idx_request_filters_request_id'],
        ['request_filters', ['provider_id'],       'idx_request_filters_provider_id'],

        // user_request_payments - revenue queries
        ['user_request_payments', ['request_id'],  'idx_user_request_payments_request_id'],

        // provider_devices - geolocation polling
        ['provider_devices', ['provider_id'],      'idx_provider_devices_provider_id'],

        // providers - availability & geo queries
        ['providers', ['status'],                  'idx_providers_status'],
        ['providers', ['service_type_id'],         'idx_providers_service_type_id'],

        // users - lookup by mobile
        ['users', ['mobile'],                      'idx_users_mobile'],

        // service_types - category/variant filtering
        ['service_types', ['service_id'],          'idx_service_types_service_id'],
    ];

    /**
     * Run the migrations.
     */
    public function up()
    {
        foreach ($this->indexes as [$table, $columns, $name]) {
            // Skip if table doesn't exist (safer for partial installs)
            if (!Schema::hasTable($table)) {
                continue;
            }

            // Skip if ALL columns don't exist
            $allExist = true;
            foreach ($columns as $col) {
                if (!Schema::hasColumn($table, $col)) {
                    $allExist = false;
                    break;
                }
            }
            if (!$allExist) continue;

            try {
                Schema::table($table, function (Blueprint $table_blueprint) use ($columns, $name) {
                    $table_blueprint->index($columns, $name);
                });
            } catch (\Exception $e) {
                // Index already exists or DB error — skip gracefully
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        foreach ($this->indexes as [$table, $columns, $name]) {
            if (!Schema::hasTable($table)) continue;

            try {
                Schema::table($table, function (Blueprint $table_blueprint) use ($name) {
                    $table_blueprint->dropIndex($name);
                });
            } catch (\Exception $e) {
                // Already gone — skip
            }
        }
    }
}
