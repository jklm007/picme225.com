#!/bin/bash
# Write the fixed DatabaseSeeder.php directly into the pod

POD="laravel-deployment-7b87f5f49c-fhkgv"

sudo k3s kubectl exec $POD -- bash -c 'cat > /app/database/seeders/DatabaseSeeder.php << '"'"'PHPEOF'"'"'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        if (DB::getDriverName() === "mysql") {
            DB::statement("SET FOREIGN_KEY_CHECKS=0;");
        } elseif (DB::getDriverName() === "pgsql") {
            DB::statement("SET session_replication_role = '"'"'replica'"'"';");
        }

        $this->call([
            AdminsTableSeeder::class,
            SettingsTableSeeder::class,
            DocumentsTableSeeder::class,
            PicmeServiceTypesSeeder::class,
            CustomServiceTypesSeeder::class,
            CustomProvidersSeeder::class,
            SocialMarketplaceSeeder::class,
            PrivateAdSeeder::class,
            MarketplacePlansSeeder::class,
            PdpStopsSeeder::class,
            PdpRoutesSeeder::class,
            DemoSeeder::class,
            AccountsSeeder::class,
            FleetSeeder::class,
            DispatcherSeeder::class,
        ]);

        if (DB::getDriverName() === "mysql") {
            DB::statement("SET FOREIGN_KEY_CHECKS=1;");
        } elseif (DB::getDriverName() === "pgsql") {
            DB::statement("SET session_replication_role = '"'"'origin'"'"';");
        }
    }
}
PHPEOF
echo "Write OK"
'
