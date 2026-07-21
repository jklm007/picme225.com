# install_server.ps1
# Script d'installation automatique pour le backend Tranxit (Laravel + Node.js)

Write-Host "--- Début de l'installation du Backend Tranxit ---" -ForegroundColor Cyan

# 1. Vérification des prérequis
$prereqs = @("php", "composer", "node", "npm")
foreach ($cmd in $prereqs) {
    if (-not (Get-Command $cmd -ErrorAction SilentlyContinue)) {
        Write-Warning "ERREUR : $cmd n'est pas installé ou n'est pas dans le PATH."
        exit
    }
}
Write-Host "[OK] Prérequis vérifiés (PHP, Composer, Node.js)" -ForegroundColor Green

# 2. Configuration du fichier .env
if (-not (Test-Path ".env")) {
    Write-Host "Création du fichier .env à partir de .env.example..."
    Copy-Item ".env.example" ".env"
    Write-Host "[OK] Fichier .env créé. Veuillez vérifier vos accès MySQL dedans." -ForegroundColor Yellow
}

# 3. Installation des dépendances PHP
Write-Host "Installation des dépendances Composer (PHP)..."
composer install --no-interaction
Write-Host "[OK] Composer terminé" -ForegroundColor Green

# 4. Installation des dépendances Node.js
Write-Host "Installation des dépendances NPM (Node.js)..."
npm install
Write-Host "[OK] NPM terminé" -ForegroundColor Green

# 5. Configuration de l'application
Write-Host "Génération de la clé d'application et configuration Passport..."
php artisan key:generate
php artisan storage:link

# 6. Base de données (Demande à l'utilisateur)
$dbResp = "y" # default as we expect user to want this
if ($dbResp -eq "y") {
    Write-Host "Exécution des migrations..."
    php artisan migrate --force
    php artisan passport:install
    Write-Host "[OK] Base de données configurée." -ForegroundColor Green
}

Write-Host "`n--- Installation Terminée ! ---" -ForegroundColor Cyan
Write-Host "Pour lancer le serveur Laravel : php artisan serve"
Write-Host "Pour lancer le serveur Socket : node server.js" -ForegroundColor Yellow
