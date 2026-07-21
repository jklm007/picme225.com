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
        Schema::table('user_request_passengers', function (Blueprint $table) {
            if (Schema::hasColumn('user_request_passengers', 'user_request_id') &&
                !Schema::hasColumn('user_request_passengers', 'request_id')) {
                $table->unsignedInteger('request_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('user_request_passengers', 'baggage_count')) {
                $table->integer('baggage_count')->default(0)->after('segment_type');
            }
        });

        if (Schema::hasColumn('user_request_passengers', 'request_id') &&
            Schema::hasColumn('user_request_passengers', 'user_request_id')) {
            DB::table('user_request_passengers')->update([
                'request_id' => DB::raw('user_request_id'),
            ]);

            Schema::table('user_request_passengers', function (Blueprint $table) {
                $table->foreign('request_id')
                    ->references('id')
                    ->on('user_requests')
                    ->onDelete('cascade');

                $table->dropForeign(['user_request_id']);
                $table->dropColumn('user_request_id');

                $table->unsignedInteger('request_id')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_request_passengers', function (Blueprint $table) {
            if (Schema::hasColumn('user_request_passengers', 'request_id') &&
                !Schema::hasColumn('user_request_passengers', 'user_request_id')) {
                $table->unsignedInteger('user_request_id')->nullable()->after('id');
            }

            if (Schema::hasColumn('user_request_passengers', 'baggage_count')) {
                $table->dropColumn('baggage_count');
            }
        });

        if (Schema::hasColumn('user_request_passengers', 'user_request_id') &&
            Schema::hasColumn('user_request_passengers', 'request_id')) {
            DB::table('user_request_passengers')->update([
                'user_request_id' => DB::raw('request_id'),
            ]);

            Schema::table('user_request_passengers', function (Blueprint $table) {
                $table->foreign('user_request_id')
                    ->references('id')
                    ->on('user_requests')
                    ->onDelete('cascade');

                $table->dropForeign(['request_id']);
                $table->dropColumn('request_id');

                $table->unsignedInteger('user_request_id')->nullable(false)->change();
            });
        }
    }
};

