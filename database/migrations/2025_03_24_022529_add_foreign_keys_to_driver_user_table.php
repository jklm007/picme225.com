<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function indexExists(string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            return collect(DB::select("SHOW INDEX FROM driver_user WHERE Key_name = ?", [$indexName]))->count() > 0;
        }
        // PostgreSQL
        return collect(DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'driver_user' AND indexname = ?", [$indexName]))->count() > 0;
    }

    public function up()
    {
        Schema::table('driver_user', function (Blueprint $table) {
            if (!$this->indexExists('driver_user_driver_id_user_id_created_at_unique')) {
                $table->unique(['driver_id', 'user_id', 'created_at'], 'driver_user_driver_id_user_id_created_at_unique');
            }
        });
    }

    public function down()
    {
        Schema::table('driver_user', function (Blueprint $table) {
            if ($this->indexExists('driver_user_driver_id_user_id_created_at_unique')) {
                $table->dropUnique('driver_user_driver_id_user_id_created_at_unique');
            }
        });
    }
};

