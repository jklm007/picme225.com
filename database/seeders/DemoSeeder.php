<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks for the duration of this seeder


        // All your truncate statements
        // DB::table('cards')->truncate();
        // DB::table('promocodes')->truncate();
        // DB::table('promocode_usages')->truncate();
        // DB::table('provider_devices')->truncate();
        // DB::table('provider_documents')->truncate();
        // DB::table('provider_profiles')->truncate();
        // DB::table('provider_services')->truncate();
        // DB::table('request_filters')->truncate();
        // DB::table('user_request_payments')->truncate();
        // DB::table('user_request_ratings')->truncate();
        // DB::table('user_requests')->truncate();
        // DB::table('users')->truncate(); // This is the problematic one

        // Users data
        $users = [[
            'first_name' => 'Appoets',
            'last_name' => 'Demo',
            'email' => 'demo@demo.com',
            'password' => bcrypt('123456'),
            'mobile' => '0759747444',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'picture' => 'http://lorempixel.com/512/512/business/Tranxit',
            'payment_mode' => 'CASH',
            'device_type' => 'android',
            'login_by' => 'manual',
        ],[
            'first_name' => 'Emilia',
            'last_name' => 'Epps',
            'email' => 'emilia@demo.com',
            'password' => bcrypt('123456'),
            'mobile' => '0758286571',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'picture' => 'http://lorempixel.com/512/512/business/Tranxit',
            'payment_mode' => 'CASH',
            'device_type' => 'android',
            'login_by' => 'manual',
        ],[
            'first_name' => 'Perry',
            'last_name' => 'Kingsley',
            'email' => 'perry@demo.com',
            'password' => bcrypt('123456'),
            'mobile' => '9258632179',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'picture' => 'http://lorempixel.com/512/512/business/Tranxit',
            'payment_mode' => 'CASH',
            'device_type' => 'android',
            'login_by' => 'manual',
        ],[
            'first_name' => 'Joseph',
            'last_name' => 'Garrison',
            'email' => 'joseph@demo.com',
            'password' => bcrypt('123456'),
            'mobile' => '9258635689',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'picture' => 'http://lorempixel.com/512/512/business/Tranxit',
            'payment_mode' => 'CASH',
            'device_type' => 'android',
            'login_by' => 'manual',
        ],[
            'first_name' => 'Ella',
            'last_name' => 'Morrissey',
            'email' => 'morrissey@demo.com',
            'password' => bcrypt('123456'),
            'mobile' => '9258678452',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'picture' => 'http://lorempixel.com/512/512/business/Tranxit',
            'payment_mode' => 'CASH',
            'device_type' => 'android',
            'login_by' => 'manual',
        ]];

        // Users data safe insert
        foreach ($users as $user) {
            if (!DB::table('users')->where('email', $user['email'])->exists()) {
                DB::table('users')->insert($user);
            }
        }

        // Providers data - added 'commune' field
        // DB::table('providers')->truncate();
        $providers = [[
            'first_name' => 'Appoets',
            'last_name' => 'Demo',
            'email' => 'demo@demo.com',
            'password' => bcrypt('123456'),
            'mobile' => '0759747444',
            'login_by' => 'manual',
            'status' => 'approved',
            'latitude' => '5.3300',
            'longitude' => '-4.0000',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'avatar' => 'http://lorempixel.com/512/512/business/Tranxit',
            'commune' => 'Cocody',
        ],[
            'first_name' => 'Thomas',
            'last_name' => 'Jenkins',
            'email' => 'thomas@demo.com',
            'password' => bcrypt('123456'),
            'mobile' => '0758286571',
            'login_by' => 'manual',
            'status' => 'approved',
            'latitude' => '5.3200',
            'longitude' => '-4.0200',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'avatar' => 'http://lorempixel.com/512/512/business/Tranxit',
            'commune' => 'Plateau',
        ],[
            'first_name' => 'Rachel',
            'last_name' => 'Burns',
            'email' => 'rachel@demo.com',
            'password' => bcrypt('123456'),
            'mobile' => '8465562224',
            'login_by' => 'manual',
            'status' => 'approved',
            'latitude' => '5.3400',
            'longitude' => '-4.0700',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'avatar' => 'http://lorempixel.com/512/512/business/Tranxit',
            'commune' => 'Yopougon',
        ],[
            'first_name' => 'Lorraine',
            'last_name' => 'Harris',
            'email' => 'lorraine@demo.com',
            'password' => bcrypt('123456'),
            'mobile' => '8465562225',
            'login_by' => 'manual',
            'status' => 'approved',
            'latitude' => '5.3000',
            'longitude' => '-3.9800',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'avatar' => 'http://lorempixel.com/512/512/business/Tranxit',
            'commune' => 'Marcory',
        ],[
            'first_name' => 'Adam',
            'last_name' => 'Wagner',
            'email' => 'wagner@demo.com',
            'password' => bcrypt('123456'),
            'mobile' => '8465562226',
            'login_by' => 'manual',
            'status' => 'approved',
            'latitude' => '5.4200',
            'longitude' => '-4.0200',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'avatar' => 'http://lorempixel.com/512/512/business/Tranxit',
            'commune' => 'Abobo',
        ]];

        foreach ($providers as $provider) {
            if (!DB::table('providers')->where('email', $provider['email'])->exists()) {
                DB::table('providers')->insert($provider);
            }
        }

        // Provider Services data - use dynamic IDs to avoid hardcoded ID issues
        $firstServiceType = DB::table('service_types')->orderBy('id')->first();
        if ($firstServiceType) {
            foreach (['demo@demo.com', 'thomas@demo.com'] as $email) {
                $provider = DB::table('providers')->where('email', $email)->first();
                if ($provider && !DB::table('provider_services')->where('provider_id', $provider->id)->exists()) {
                    DB::table('provider_services')->insert([
                        'provider_id' => $provider->id,
                        'service_type_id' => $firstServiceType->id,
                        'status' => 'active',
                        'service_number' => '4ppo3ts',
                        'service_model' => 'Audi R8',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }

        // Re-enable foreign key checks after all truncates and inserts are done

    }
}
