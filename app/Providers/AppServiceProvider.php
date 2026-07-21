<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Dynamically alias legacy model references (e.g. App\Service) to App\Models namespace on the fly
        spl_autoload_register(function ($class) {
            if (strpos($class, 'App\\') === 0 && strpos($class, 'App\\Models\\') !== 0) {
                $parts = explode('\\', $class);
                if (count($parts) === 2) {
                    $modelName = $parts[1];
                    $targetClass = "App\\Models\\{$modelName}";
                    if (class_exists($targetClass)) {
                        class_alias($targetClass, $class);
                    }
                }
            }
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \App\Models\Provider::observe(\App\Observers\ProviderObserver::class);

        // Force HTTPS in production (Cloudflare Tunnel / K8s) — skip in local dev
        if (!app()->isLocal()) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Trust all proxies for Cloudflare Tunnel support
        \Illuminate\Http\Request::setTrustedProxies(
            ['0.0.0.0/0', '2000::/3'], // Trust all IPv4 and IPv6
            \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB
        );

        // Dynamically override configurations with Dashboard Settings
        try {
            if (\Schema::hasTable('settings')) {
                // R2 overrides
                if ($r2Key = \Setting::get('r2_access_key')) {
                    config(['filesystems.disks.s3.key' => $r2Key]);
                    // Force the default disk to s3 so uploads use R2 automatically
                    config(['filesystems.default' => 's3']);
                    // For controllers that use env() directly, we must also update the env var in memory
                    putenv("FILESYSTEM_DISK=s3");
                    $_ENV['FILESYSTEM_DISK'] = 's3';
                    $_SERVER['FILESYSTEM_DISK'] = 's3';
                }
                if ($r2Secret = \Setting::get('r2_secret_key')) config(['filesystems.disks.s3.secret' => $r2Secret]);
                if ($r2Endpoint = \Setting::get('r2_endpoint')) config(['filesystems.disks.s3.endpoint' => $r2Endpoint]);
                if ($r2Bucket = \Setting::get('r2_bucket')) config(['filesystems.disks.s3.bucket' => $r2Bucket]);
                if ($r2Url = \Setting::get('r2_url')) config(['filesystems.disks.s3.url' => $r2Url]);
                
                // WhatsApp Evolution overrides
                if ($evoUrl = \Setting::get('evolution_api_url')) config(['services.evolution.url' => $evoUrl]);
                if ($evoKey = \Setting::get('evolution_api_key')) config(['services.evolution.key' => $evoKey]);
                if ($evoInst = \Setting::get('evolution_instance')) config(['services.evolution.instance' => $evoInst]);

                // Google Ads overrides
                if ($adsClientId = \Setting::get('google_ads_client_id')) config(['services.google_ads.client_id' => $adsClientId]);
                if ($adsSecret = \Setting::get('google_ads_client_secret')) config(['services.google_ads.client_secret' => $adsSecret]);
                if ($adsToken = \Setting::get('google_ads_developer_token')) config(['services.google_ads.developer_token' => $adsToken]);
                if ($adsCustomer = \Setting::get('google_ads_customer_id')) config(['services.google_ads.customer_id' => $adsCustomer]);
            }
        } catch (\Exception $e) {
            // Fails gracefully if settings table is not migrated yet
        }
    }
}
