#!/bin/bash

# ==============================================================================
# SCRIPT DE DÉPLOIEMENT AUTOMATISÉ - PICME BACKEND
# ==============================================================================
# Ce script installe les dépendances, configure la DB et lance les services.
# ==============================================================================

set -e

# --- CONFIGURATION (Modifiez ici si besoin) ---
DB_NAME="transport"
DB_USER="admin"
DB_PASS="votre_mot_de_passe_secret" # CHANGEZ CECI !
PROJECT_DIR="/var/www/picme-backend"
DOMAIN="picme225.site"

echo "--------------------------------------------------------"
echo "  DÉMARRAGE DE L'INSTALLATION AUTOMATIQUE PICME"
echo "  DOMAINE : $DOMAIN"
echo "--------------------------------------------------------"

# 1. Mise à jour et installation des outils de base
echo "--- Mise à jour du système et installation des outils ---"
sudo apt-get update && sudo apt-get upgrade -y
sudo apt-get install -y software-properties-common curl zip unzip git wget build-essential nginx certbot python3-certbot-nginx

# 2. Installation PHP 8.1
echo "--- Installation de PHP 8.1 ---"
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update
sudo apt-get install -y php8.1-fpm php8.1-cli php8.1-common php8.1-mysql php8.1-xml php8.1-curl php8.1-gd php8.1-mbstring php8.1-zip php8.1-bcmath php8.1-redis

# 3. Installation Composer & Node.js
echo "--- Installation de Composer et Node.js ---"
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
sudo npm install -g pm2

# 4. Installation MySQL & Création DB
echo "--- Installation de MySQL et création de la base ---"
sudo apt-get install -y mysql-server
sudo mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"
sudo mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
sudo mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# 5. Configuration du Dossier Projet
echo "--- Configuration du dossier projet ---"
sudo mkdir -p $PROJECT_DIR
# On copie le contenu actuel vers le dossier de destination
sudo cp -r . $PROJECT_DIR
cd $PROJECT_DIR
sudo chown -R $USER:www-data $PROJECT_DIR
sudo chmod -R 775 $PROJECT_DIR

# 6. Installation des Dépendances (Laravel & Node)
echo "--- Installation des dépendances ---"
composer install --no-interaction --optimize-autoloader
npm install --production

# 7. Configuration .env et Initialisation Laravel
echo "--- Initialisation de Laravel ---"
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# Remplacement automatique des accès DB et URL dans le .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" .env
sed -i "s|APP_URL=.*|APP_URL=https://${DOMAIN}|" .env

# Initialisation Passport
echo "--- Configuration Passport ---"
php artisan passport:keys --force

# 1. Client pour l'App Utilisateur (User)
php artisan passport:client --password --name="User App Client" --provider="users" --no-interaction > passport_user.txt
USER_ID=$(grep "Client ID" passport_user.txt | awk '{print $NF}')
USER_SECRET=$(grep "Client secret" passport_user.txt | awk '{print $NF}')

# 2. Client pour l'App Chauffeur (Driver)
php artisan passport:client --password --name="Driver App Client" --provider="providers" --no-interaction > passport_driver.txt
DRIVER_ID=$(grep "Client ID" passport_driver.txt | awk '{print $NF}')
DRIVER_SECRET=$(grep "Client secret" passport_driver.txt | awk '{print $NF}')

php artisan key:generate
php artisan migrate --force

# Correction manuelle de l'ENUM calculator pour le seeder
echo "--- Application des correctifs SQL ---"
sudo mysql -u ${DB_USER} -p${DB_PASS} ${DB_NAME} -e "ALTER TABLE service_service_type MODIFY calculator ENUM('MIN','HOUR','DISTANCE','DISTANCEMIN','DISTANCEDAY', 'SHARED', 'DISTANCEHOUR', 'DAY');"

php artisan db:seed --force
rm -rf public/storage
php artisan storage:link

# 8. Permissions pour le serveur web
echo "--- Configuration des permissions ---"
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 9. Configuration NGINX
echo "--- Configuration Nginx pour $DOMAIN ---"
sudo tee /etc/nginx/sites-available/picme-backend <<EOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;
    root $PROJECT_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # Proxy pour Socket.io (Port 3000)
    location /socket.io {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host \$host;
        proxy_cache_bypass \$http_upgrade;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

sudo ln -sf /etc/nginx/sites-available/picme-backend /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx

# 10. Lancement du Serveur Socket avec PM2
echo "--- Lancement du Serveur Socket.io avec PM2 ---"
if [ -f "server.js" ]; then
    pm2 stop all || true
    pm2 start server.js --name "picme-socket"
    pm2 save
    pm2 startup
fi

echo "--------------------------------------------------------"
echo " INSTALLATION TERMINÉE ET SERVICES LANCÉS !"
echo "--------------------------------------------------------"
echo " IMPORTANT: ÉTAPES FINALES"
echo "--------------------------------------------------------"
echo " 1. Pointez votre domaine $DOMAIN vers l'IP de ce serveur."
echo " 2. Activez le SSL (HTTPS) avec cette commande :"
echo "    sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN"
echo "--------------------------------------------------------"
echo " Pour l'App UTILISATEUR (User) :"
echo "   CLIENT_ID : $USER_ID"
echo "   CLIENT_SECRET : $USER_SECRET"
echo ""
echo " Pour l'App CHAUFFEUR (Driver) :"
echo "   CLIENT_ID : $DRIVER_ID"
echo "   CLIENT_SECRET : $DRIVER_SECRET"
echo "--------------------------------------------------------"

