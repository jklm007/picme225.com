<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy', // Mise à jour pour les modèles modernes
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // Fix OpenSSL hang on Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            putenv('OPENSSL_CONF=C:\xampp\php\extras\ssl\openssl.cnf');
        }

        $this->registerPolicies();

        // Vérifiez si la méthode `ignoreRoutes` existe
        if (method_exists(Passport::class, 'ignoreRoutes')) {
            Passport::ignoreRoutes(); // Indique que les routes Passport doivent être définies manuellement
        }

        Passport::enablePasswordGrant();

        // Configurez les expirations des tokens
        Passport::tokensExpireIn(Carbon::now()->addDays(15));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(90));

        // Ajoutez éventuellement des scopes personnalisés ici
        Passport::tokensCan([
            'view-profile' => 'View user profile',
            'edit-profile' => 'Edit user profile',
        ]);
    }
}
