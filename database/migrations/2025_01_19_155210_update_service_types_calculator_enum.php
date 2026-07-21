<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateServiceTypesCalculatorEnum extends Migration
{
    public function up()
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE service_types MODIFY calculator ENUM(
                'MIN', 'HOUR', 'DISTANCE', 'DISTANCEMIN', 'DISTANCEHOUR', 'DAY', 'DISTANCEDAY', 'SHARED'
            )");
        } else {
            // PostgreSQL: drop any existing calculator constraint first, then skip (column is already text/varchar)
            // Suppression de toutes les contraintes CHECK existantes sur la colonne calculator
            $constraints = DB::select("
                SELECT con.conname
                FROM pg_constraint con
                INNER JOIN pg_class rel ON rel.oid = con.conrelid
                INNER JOIN pg_namespace nsp ON nsp.oid = con.connamespace
                WHERE con.contype = 'c'
                AND rel.relname = 'service_types'
                AND pg_get_constraintdef(con.oid) LIKE '%calculator%'
            ");
            foreach ($constraints as $constraint) {
                DB::statement("ALTER TABLE service_types DROP CONSTRAINT IF EXISTS \"{$constraint->conname}\"");
            }
            // No need to add an ENUM constraint - PostgreSQL uses TEXT column which accepts any value
        }
    }

    public function down()
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE service_types MODIFY calculator ENUM(
                'MIN', 'HOUR', 'DISTANCE', 'DISTANCEMIN', 'DISTANCEHOUR'
            )");
        }
        // PostgreSQL: no-op
    }
}


