#!/usr/bin/env php
<?php

/**
 * Script de Vérification des Fonctionnalités - Picme225.com
 * 
 * Ce script vérifie que tous les composants critiques sont opérationnels
 */

echo "🔍 Vérification des Fonctionnalités - Picme225.com\n";
echo "================================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Vérifier PHP Version
echo "1️⃣  Vérification de PHP...\n";
$phpVersion = phpversion();
if (version_compare($phpVersion, '8.1.0', '>=')) {
    $success[] = "✅ PHP version: $phpVersion";
} else {
    $errors[] = "❌ PHP version insuffisante: $phpVersion (requis: >= 8.1.0)";
}

// 2. Vérifier les extensions PHP requises
echo "\n2️⃣  Vérification des extensions PHP...\n";
$requiredExtensions = [
    'pdo',
    'pdo_mysql',
    'mbstring',
    'openssl',
    'tokenizer',
    'xml',
    'ctype',
    'json',
    'bcmath',
    'curl',
    'gd',
    'fileinfo'
];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        $success[] = "✅ Extension $ext: installée";
    } else {
        $errors[] = "❌ Extension $ext: manquante";
    }
}

// 3. Vérifier la structure des dossiers
echo "\n3️⃣  Vérification de la structure des dossiers...\n";
$requiredDirs = [
    'app',
    'app/Http/Controllers',
    'app/Models',
    'app/Services',
    'database/migrations',
    'routes',
    'config',
    'storage',
    'storage/app',
    'storage/logs',
    'public',
    'resources'
];

foreach ($requiredDirs as $dir) {
    if (is_dir(__DIR__ . '/../' . $dir)) {
        $success[] = "✅ Dossier $dir: existe";
    } else {
        $errors[] = "❌ Dossier $dir: manquant";
    }
}

// 4. Vérifier les fichiers critiques
echo "\n4️⃣  Vérification des fichiers critiques...\n";
$requiredFiles = [
    'composer.json',
    'package.json',
    'artisan',
    'routes/api.php',
    'routes/web.php',
    'app/Http/Controllers/UserApiController.php',
    'app/Http/Controllers/UserSharedRideController.php',
    'app/Http/Controllers/Dao/ProposalController.php',
    'app/Http/Controllers/EcoToken/TokenController.php',
    'app/Http/Controllers/MobileMoney/PaymentController.php',
    'app/Http/Controllers/AdCampaignController.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists(__DIR__ . '/../' . $file)) {
        $success[] = "✅ Fichier $file: existe";
    } else {
        $errors[] = "❌ Fichier $file: manquant";
    }
}

// 5. Vérifier les modèles
echo "\n5️⃣  Vérification des modèles...\n";
$requiredModels = [
    'User',
    'Provider',
    'UserRequests',
    'ServiceType',
    'ActiveSharedRide',
    'RideBooking',
    'PdpRoute',
    'PdpStop',
    'DaoProposal',
    'DaoVote',
    'EcoTokenTransaction',
    'MobileMoneyTransaction',
    'AdCampaign'
];

foreach ($requiredModels as $model) {
    $modelFile = "app/$model.php";
    if (file_exists(__DIR__ . '/../' . $modelFile)) {
        $success[] = "✅ Modèle $model: existe";
    } else {
        $warnings[] = "⚠️  Modèle $model: non trouvé à $modelFile";
    }
}

// 6. Vérifier les services
echo "\n6️⃣  Vérification des services...\n";
$requiredServices = [
    'app/Services/EcoTokenService.php',
    'app/Services/Web3Service.php',
    'app/Services/MobileMoneyService.php',
    'app/Services/AiAdService.php',
    'app/Services/AdPlatformService.php',
    'app/Services/SharedTripService.php'
];

foreach ($requiredServices as $service) {
    if (file_exists(__DIR__ . '/../' . $service)) {
        $success[] = "✅ Service " . basename($service) . ": existe";
    } else {
        $errors[] = "❌ Service " . basename($service) . ": manquant";
    }
}

// 7. Vérifier les migrations critiques
echo "\n7️⃣  Vérification des migrations...\n";
$migrationPatterns = [
    'create_users_table',
    'create_providers_table',
    'create_user_requests_table',
    'create_service_types_table',
    'create_active_shared_rides_table',
    'create_ride_bookings_table',
    'create_pdp_routes_table',
    'create_dao_proposals_table',
    'create_eco_token_transactions_table',
    'create_mobile_money_transactions_table',
    'create_ad_campaigns_table'
];

$migrationsDir = __DIR__ . '/../database/migrations';
if (is_dir($migrationsDir)) {
    $migrations = scandir($migrationsDir);
    foreach ($migrationPatterns as $pattern) {
        $found = false;
        foreach ($migrations as $migration) {
            if (strpos($migration, $pattern) !== false) {
                $success[] = "✅ Migration $pattern: existe";
                $found = true;
                break;
            }
        }
        if (!$found) {
            $warnings[] = "⚠️  Migration $pattern: non trouvée";
        }
    }
}

// 8. Vérifier les permissions
echo "\n8️⃣  Vérification des permissions...\n";
$writableDirs = [
    'storage',
    'storage/app',
    'storage/framework',
    'storage/logs',
    'bootstrap/cache'
];

foreach ($writableDirs as $dir) {
    $fullPath = __DIR__ . '/../' . $dir;
    if (is_dir($fullPath) && is_writable($fullPath)) {
        $success[] = "✅ Dossier $dir: accessible en écriture";
    } else {
        $errors[] = "❌ Dossier $dir: non accessible en écriture";
    }
}

// 9. Vérifier composer.json
echo "\n9️⃣  Vérification des dépendances...\n";
$composerFile = __DIR__ . '/../composer.json';
if (file_exists($composerFile)) {
    $composer = json_decode(file_get_contents($composerFile), true);
    
    $requiredPackages = [
        'laravel/framework',
        'laravel/passport',
        'kreait/firebase-php',
        'stripe/stripe-php'
    ];
    
    foreach ($requiredPackages as $package) {
        if (isset($composer['require'][$package])) {
            $success[] = "✅ Package $package: " . $composer['require'][$package];
        } else {
            $warnings[] = "⚠️  Package $package: non trouvé dans composer.json";
        }
    }
}

// 10. Vérifier les routes API
echo "\n🔟 Vérification des routes API...\n";
$apiRoutesFile = __DIR__ . '/../routes/api.php';
if (file_exists($apiRoutesFile)) {
    $apiRoutes = file_get_contents($apiRoutesFile);
    
    $criticalRoutes = [
        '/signin',
        '/signup',
        '/send/request',
        '/shared/rides/nearby',
        '/dao/proposals',
        '/eco-token/balance',
        '/mobile-money/payment/initiate',
        '/ad-campaigns'
    ];
    
    foreach ($criticalRoutes as $route) {
        if (strpos($apiRoutes, $route) !== false) {
            $success[] = "✅ Route $route: définie";
        } else {
            $warnings[] = "⚠️  Route $route: non trouvée";
        }
    }
}

// Affichage du résumé
echo "\n\n" . str_repeat("=", 50) . "\n";
echo "📊 RÉSUMÉ DE LA VÉRIFICATION\n";
echo str_repeat("=", 50) . "\n\n";

echo "✅ Succès: " . count($success) . "\n";
echo "⚠️  Avertissements: " . count($warnings) . "\n";
echo "❌ Erreurs: " . count($errors) . "\n\n";

if (count($errors) > 0) {
    echo "❌ ERREURS CRITIQUES:\n";
    echo str_repeat("-", 50) . "\n";
    foreach ($errors as $error) {
        echo "$error\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "⚠️  AVERTISSEMENTS:\n";
    echo str_repeat("-", 50) . "\n";
    foreach ($warnings as $warning) {
        echo "$warning\n";
    }
    echo "\n";
}

// Conclusion
echo str_repeat("=", 50) . "\n";
if (count($errors) === 0) {
    echo "✅ VÉRIFICATION RÉUSSIE!\n";
    echo "Le projet semble correctement configuré.\n";
    exit(0);
} else {
    echo "❌ VÉRIFICATION ÉCHOUÉE!\n";
    echo "Veuillez corriger les erreurs ci-dessus.\n";
    exit(1);
}
