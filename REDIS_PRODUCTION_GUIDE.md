# 🚀 Guide de Production : Déploiement de Redis sur Serveur Linux (VPS/Dédié)

Ce document décrit la procédure étape par étape pour installer et configurer **Redis** sur votre futur serveur de production (généralement sous Ubuntu ou Debian), afin de garantir des performances maximales pour le backend PicMe.

---

## 1. Installation de Redis Server

Sur un serveur Linux, Redis est très facile à installer via le gestionnaire de paquets officiel. Connectez-vous à votre serveur via SSH et exécutez :

```bash
sudo apt update
sudo apt install redis-server -y
```

Vérifiez que Redis fonctionne correctement :
```bash
sudo systemctl status redis-server
```
*(Le statut doit indiquer `active (running)`)*

---

## 2. Sécurisation de Redis (Très Important)

Par défaut, Redis n'a pas de mot de passe. Si votre port Redis (6379) est exposé à Internet (ce qui ne devrait pas être le cas, mais par précaution), il faut le protéger.

Ouvrez le fichier de configuration :
```bash
sudo nano /etc/redis/redis.conf
```

1. **Assurez-vous qu'il n'écoute qu'en local** :
   Cherchez la ligne `bind 127.0.0.1 -::1` et assurez-vous qu'elle n'est pas commentée (pas de `#` devant).
2. **Ajoutez un mot de passe** :
   Cherchez la ligne `# requirepass foobared`, décommentez-la et remplacez `foobared` par un mot de passe très fort :
   ```conf
   requirepass VOTRE_MOT_DE_PASSE_TRES_COMPLEXE
   ```
3. Sauvegardez (`Ctrl+O`, `Entrée`) et quittez (`Ctrl+X`).

Redémarrez Redis pour appliquer :
```bash
sudo systemctl restart redis-server
```

---

## 3. Configuration de Laravel (Backend PicMe)

Contrairement à Windows où nous avons utilisé `predis`, sur Linux, il est fortement recommandé d'utiliser l'extension native **PHPRedis** (écrite en C) car elle est beaucoup plus rapide.

### 3.1. Installer l'extension PHPRedis
```bash
sudo apt install php-redis -y
sudo systemctl restart php8.1-fpm  # Remplacez 8.1 par votre version de PHP
sudo systemctl restart nginx       # ou apache2 selon votre serveur web
```

### 3.2. Mettre à jour le `.env` de production
Dans le dossier de votre projet Laravel (`/var/www/picme225.com_backend`), modifiez le `.env` :

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=phpredis  # IMPORTANT : phpredis au lieu de predis en production
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=VOTRE_MOT_DE_PASSE_TRES_COMPLEXE
REDIS_PORT=6379
```

---

## 4. Gérer les Files d'Attente (Queues) avec Supervisor

Sur votre PC Windows, le script `LANCER_PICKME.py` lance un processus en arrière-plan. En production, on utilise **Supervisor** pour s'assurer que le `queue:work` tourne en permanence 24h/24 et 7j/7, et redémarre automatiquement s'il plante.

### 4.1. Installer Supervisor
```bash
sudo apt install supervisor -y
```

### 4.2. Créer la configuration pour PicMe
Créez un nouveau fichier de configuration :
```bash
sudo nano /etc/supervisor/conf.d/picme-worker.conf
```

Collez-y ceci (adaptez le chemin `/var/www/picme...`) :
```ini
[program:picme-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/picme225.com_backend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/picme225.com_backend/storage/logs/worker.log
stopwaitsecs=3600
```
> *Note : `numprocs=2` lancera 2 travailleurs (workers) en parallèle pour traiter les notifications push et autres tâches encore plus vite.*

### 4.3. Démarrer le Worker
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start picme-worker:*
```

---

## 5. Résumé des Commandes d'Optimisation

À chaque fois que vous déployez du code en production, exécutez toujours ces commandes pour que Laravel reconstruise son cache (qui ira se stocker ultra-rapidement dans Redis) :

```bash
cd /var/www/picme225.com_backend
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

Avec cette configuration, votre serveur sera capable de gérer **des milliers de requêtes par seconde** sans latence ! 🚀
