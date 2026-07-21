# Guide de Migration - Système PDP et Campagnes Publicitaires

## Problème rencontré

Les erreurs SQL indiquent que les tables n'existent pas dans la base de données. Il faut exécuter les migrations.

## Solution

### 1. Vérifier l'état des migrations

```bash
php artisan migrate:status
```

### 2. Exécuter toutes les migrations

```bash
php artisan migrate
```

### 3. Si des erreurs persistent, réinitialiser (ATTENTION: supprime les données)

```bash
php artisan migrate:fresh --seed
```

## Ordre des migrations importantes

Les migrations doivent être exécutées dans cet ordre :

1. `2025_11_19_000002_create_pdp_routes_table.php` - Créer la table des itinéraires
2. `2025_07_11_053015_create_pdp_stops_table.php` - Créer la table des arrêts (corrigée)
3. `2025_11_19_000003_add_route_info_to_pdp_stops_table.php` - Ajouter pdp_route_id aux arrêts
4. `2025_11_19_000008_create_pdp_route_segments_table.php` - Créer la table des segments
5. `2025_11_20_000006_add_service_type_to_pdp_route_segments_table.php` - Ajouter service_type_id aux segments
6. `2025_11_19_000004_create_pdp_route_votes_table.php` - Créer la table des votes
7. `2025_11_19_000005_create_active_shared_rides_table.php` - Créer la table des trajets actifs
8. `2025_11_19_000006_create_ride_bookings_table.php` - Créer la table des réservations
9. `2025_11_19_000007_add_service_type_to_active_shared_rides_table.php` - Ajouter service_type_id aux trajets actifs
10. `2025_11_20_000007_create_ad_campaigns_table.php` - Créer la table des campagnes publicitaires
11. `2025_11_20_000008_create_ad_contents_table.php` - Créer la table des contenus publicitaires
12. `2025_11_20_000009_create_ad_platforms_table.php` - Créer la table des plateformes
13. `2025_11_20_000010_create_campaign_performances_table.php` - Créer la table des performances
14. `2025_11_20_000011_create_ad_templates_table.php` - Créer la table des templates

## Corrections apportées

### Migration `pdp_stops` corrigée

La migration `2025_07_11_053015_create_pdp_stops_table.php` avait une syntaxe incorrecte. Elle a été corrigée pour :
- Utiliser correctement `Schema::create` dans la méthode `up()`
- Ajouter tous les champs nécessaires (description, max_waiting_time, etc.)
- Rendre `commune` nullable

### Correction dans PdpRouteSegmentResource

L'ordre de tri a été corrigé pour éviter l'erreur sur `pdp_route_id` dans la clause ORDER BY.

## Commandes utiles

```bash
# Voir l'état des migrations
php artisan migrate:status

# Exécuter les migrations
php artisan migrate

# Exécuter les migrations avec les seeders
php artisan migrate --seed

# Réinitialiser complètement (ATTENTION: supprime toutes les données)
php artisan migrate:fresh --seed

# Rollback de la dernière migration
php artisan migrate:rollback

# Rollback de toutes les migrations
php artisan migrate:reset
```

## Vérification après migration

Après avoir exécuté les migrations, vérifiez que les tables suivantes existent :

- `pdp_routes`
- `pdp_stops`
- `pdp_route_segments`
- `pdp_route_votes`
- `active_shared_rides`
- `ride_bookings`
- `ad_campaigns`
- `ad_contents`
- `ad_platforms`
- `campaign_performances`
- `ad_templates`

## Si les erreurs persistent

1. Vérifiez que vous êtes connecté à la bonne base de données dans `.env`
2. Vérifiez que les migrations sont dans le bon ordre (par date)
3. Vérifiez les logs : `storage/logs/laravel.log`
4. Exécutez `php artisan config:clear` et `php artisan cache:clear`

