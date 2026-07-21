<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        // PERFORMANCE: Optimizes API responses with cache headers
        \App\Http\Middleware\OptimizeResponse::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \App\Http\Middleware\CorsMiddleware::class,
            'throttle:120,1',
            // \Laravel\Passport\Http\Middleware\CreateFreshApiToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\LogRequests::class,
            //'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'account' => \App\Http\Middleware\RedirectIfNotAccount::class,
        'account.guest' => \App\Http\Middleware\RedirectIfAccount::class,
        'fleet' => \App\Http\Middleware\RedirectIfNotFleet::class,
        'fleet.guest' => \App\Http\Middleware\RedirectIfFleet::class,
        'dispatcher' => \App\Http\Middleware\RedirectIfNotDispatcher::class,
        'dispatcher.guest' => \App\Http\Middleware\RedirectIfDispatcher::class,
        'provider' => \App\Http\Middleware\RedirectIfNotProvider::class,
        'provider.guest' => \App\Http\Middleware\RedirectIfProvider::class,
        'admin' => \App\Http\Middleware\RedirectIfNotAdmin::class,
        'admin.guest' => \App\Http\Middleware\RedirectIfAdmin::class,
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        //      'jwt.auth' => 'Tymon\JWTAuth\Middleware\GetUserFromToken',
//        'jwt.refresh' => 'Tymon\JWTAuth\Middleware\RefreshToken',
        'demo' => \App\Http\Middleware\DemoModeMiddleware::class,
        'firebase-jwt' => \App\Http\Middleware\FirebaseJWTMiddleware::class,
        // 'language' => \App\Http\Middleware\LanguageMiddleware::class,
        'language' => \App\Http\Middleware\LandingLanguageMiddleware::class,
        'provider.language' => \App\Http\Middleware\LandingLanguageMiddleware::class,
        // 'provider.language' => \App\Http\Middleware\ProviderLanguageMiddleware::class,
        'provider.eco' => \App\Http\Middleware\ProviderEcoBalanceMiddleware::class,
        'check.subscription' => \App\Http\Middleware\CheckSubscription::class,
        'gateway.auth' => \App\Http\Middleware\GatewayAuth::class,
        'feature.level' => \App\Http\Middleware\EnsureFeatureLevel::class,
    ];
}
