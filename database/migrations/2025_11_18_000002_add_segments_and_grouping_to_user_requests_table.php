<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('user_requests', 'segments')) {
                $table->json('segments')->nullable()->after('service_type_id');
            }

            if (!Schema::hasColumn('user_requests', 'grouping_point_id')) {
                $table->unsignedBigInteger('grouping_point_id')
                    ->nullable()
                    ->after('segments');
                $table->foreign('grouping_point_id')
                    ->references('id')
                    ->on('pdp_stops')
                    ->onDelete('set null');
            }
        });

        $statuses = [
            'SEARCHING',
            'CANCELLED',
            'ACCEPTED',
            'STARTED',
            'ARRIVED',
            'PICKEDUP',
            'DROPPED',
            'COMPLETED',
            'SCHEDULED',
            'MATCHING',
            'SEGMENTING',
            'SEARCHING_MULTI',
            'ACCEPTED_MULTI',
            'IN_PROGRESS_MULTI',
            'REACHED_FIRST_STOP',
            'PENDING_PAYMENT',
        ];

        $enum = "'" . implode("','", $statuses) . "'";
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE user_requests MODIFY COLUMN status ENUM($enum) NOT NULL DEFAULT 'SEARCHING'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_requests', function (Blueprint $table) {
            if (Schema::hasColumn('user_requests', 'grouping_point_id')) {
                $table->dropForeign(['grouping_point_id']);
                $table->dropColumn('grouping_point_id');
            }

            if (Schema::hasColumn('user_requests', 'segments')) {
                $table->dropColumn('segments');
            }
        });

        $originalStatuses = [
            'SEARCHING',
            'CANCELLED',
            'ACCEPTED',
            'STARTED',
            'ARRIVED',
            'PICKEDUP',
            'DROPPED',
            'COMPLETED',
            'SCHEDULED',
        ];
        $enum = "'" . implode("','", $originalStatuses) . "'";
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE user_requests MODIFY COLUMN status ENUM($enum) NOT NULL DEFAULT 'SEARCHING'");
        }
    }
};

