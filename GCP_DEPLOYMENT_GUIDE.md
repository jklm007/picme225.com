# Guide de Déploiement Professionnel GCP - Backend PicMe

Ce guide vous explique comment déployer votre projet sur un serveur Google Cloud (Ubuntu 22.04) avec le domaine **picme225.site**.

## 1. Préparer le projet (ZIP)

1.  Assurez-vous que le fichier `setup_gcp.sh` est bien à la racine du dossier backend.
2.  Créez un ZIP de votre projet (excluez les dossiers `vendor` et `node_modules`).
    *   Nommez le fichier : `backend.zip`.

## 2. Configuration DNS (CRITIQUE)

Avant de lancer l'installation, vous devez faire pointer votre nom de domaine vers l'adresse IP de votre instance GCP :

1.  Récupérez l'**IP Externe** de votre VM sur la console Google Cloud.
2.  Chez votre fournisseur de domaine (ex: Namecheap, GoDaddy, LWS) :
    *   Ajoutez un **Enregistrement A** : Nom: `@`, Valeur: `VOTRE_IP_GCP`
    *   Ajoutez un **Enregistrement A** : Nom: `www`, Valeur: `VOTRE_IP_GCP`

## 3. Envoyer le projet sur GCP

1.  Allez dans la console **Google Cloud** -> **Compute Engine** -> **VM Instances**.
2.  Cliquez sur **SSH** pour ouvrir le terminal.
3.  Utilisez le bouton **Upload file** pour envoyer `backend.zip`.

## 4. Installation Automatisée

Une fois connecté en SSH, exécutez ces commandes :

```bash
# 1. Installer unzip
sudo apt update && sudo apt install unzip -y

# 2. Extraire le projet
unzip backend.zip -d ~/picme-backend
cd ~/picme-backend

# 3. Lancer l'installation complète (Nginx, PHP-FPM, MySQL, SSL Prep)
chmod +x setup_gcp.sh
./setup_gcp.sh
```

## 5. Activer le SSL (HTTPS)

Une fois que le script a fini et que votre DNS est propagé, activez le HTTPS :

```bash
sudo certbot --nginx -d picme225.site -d www.picme225.site
```
Suivez les instructions à l'écran (choisissez l'option pour rediriger automatiquement le trafic HTTP vers HTTPS).

## 6. Ports à ouvrir (Pare-feu GCP)

Allez dans **VPC Network** -> **Firewall** et assurez-vous que les ports suivants sont ouverts :
*   `80` (HTTP)
*   `443` (HTTPS - Nécessaire pour le domaine)
*   *Le port 3000 (Socket) est maintenant géré par Nginx via le port 443, plus besoin de l'ouvrir sur le pare-feu externe.*

---
> [!IMPORTANT]
> Le script configure maintenant **Nginx** comme serveur web de production. N'utilisez plus `php artisan serve` sur le serveur.
