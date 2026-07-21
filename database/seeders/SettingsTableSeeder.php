<?php


namespace Database\Seeders;


use Illuminate\Support\Facades\DB; 

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->delete();
        DB::table('settings')->insert([
            [
                'key' => 'site_title',
                'value' => 'PicMe'
            ],
            [
                'key' => 'site_logo',
                'value' => asset('logo-black.png'),
            ],
            [
                'key' => 'site_email_logo',
                'value' => asset('logo-white.png'),
            ],
            [
                'key' => 'site_icon',
                'value' => asset('favicon.ico'),
            ],
            [
                'key' => 'site_copyright',
                'value' => '&copy; '.date('Y').' Appoets'
            ],
            [
                'key' => 'provider_select_timeout',
                'value' => 60
            ],
            [
                'key' => 'provider_search_radius',
                'value' => 100
            ],
            [
                'key' => 'base_price',
                'value' => 50
            ],
            [
                'key' => 'price_per_minute',
                'value' => 50
            ],
            [
                'key' => 'tax_percentage',
                'value' => 0
            ],
            [
                'key' => 'stripe_secret_key',
                'value' => ''
            ],
            [
                'key' => 'stripe_publishable_key',
                'value' => ''
            ],
            [
                'key' => 'CASH',
                'value' => 1
            ],
            [
                'key' => 'CARD',
                'value' => 1
            ],
            [
                'key' => 'manual_request',
                'value' => 0
            ],
            [
                'key' => 'default_lang',
                'value' => 'fr'
            ],
            [
                'key' => 'currency',
                'value' => 'FCFA'
            ],
            [
                'key' => 'distance',
                'value' => 'Km'
            ],
            [
                'key' => 'scheduled_cancel_time_exceed',
                'value' => 10
            ],
            [
                'key' => 'price_per_kilometer',
                'value' => 10
            ],
            [
                'key' => 'commission_percentage',
                'value' => 0
            ],
            [
                'key' => 'store_link_android',
                'value' => ''
            ],
            [
                'key' => 'store_link_ios',
                'value' => ''
            ],
            [
                'key' => 'daily_target',
                'value' => 0
            ],
            [
                'key' => 'surge_percentage',
                'value' => 0
            ],
            [
                'key' => 'surge_trigger',
                'value' => 0
            ],
            [
                'key' => 'demo_mode',
                'value' => 0
            ],
            [
                'key' => 'booking_prefix',
                'value' => 'TRNX'
            ],
            [
                'key' => 'sos_number',
                'value' => '911'
            ],
            [
                'key' => 'contact_number',
                'value' => ''
            ],
            [
                'key' => 'contact_email',
                'value' => ''
            ],
            [
                'key' => 'social_login',
                'value' => 0
            ],
            [
                'key' => 'map_key',
                'value' => ''
            ],
            [
                'key' => 'fb_app_version',
                'value' => ''
            ],
            [
                'key' => 'fb_app_id',
                'value' => ''
            ],
            [
                'key' => 'fb_app_secret',
                'value' => ''
            ],
            [
                'key' => 'r2_url',
                'value' => 'https://media.picme225.site'
            ],
            [
                'key' => 'r2_access_key',
                'value' => 'ab9da087a81d703a47f95e34a0167a27'
            ],
            [
                'key' => 'r2_secret_key',
                'value' => 'f3f4f9c1a0aee212d38db9d4cb412b35e776e622289f36b1bfc508f0607cf123'
            ],
            [
                'key' => 'r2_endpoint',
                'value' => 'https://45dae7ec0d11d6baef63481feb03aa7d.r2.cloudflarestorage.com'
            ],
            [
                'key' => 'r2_bucket',
                'value' => 'picme225-storage'
            ],
        ]);
    }
}
