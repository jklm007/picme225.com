# Système de Transport Partagé Instantané - Documentation

## Vue d'ensemble

Ce document décrit l'implémentation du système de transport partagé de type "bus à la demande" où les utilisateurs peuvent voir les véhicules partagés en temps réel sur leur route, vérifier les places disponibles et réserver numériquement une place.

## Architecture

### Tables de Base de Données

#### 1. **service_types** (modifiée)
- Ajout de la colonne `type` (ENUM: `ON_DEMAND`, `SHARED_FIXED_ROUTE`)
- Permet de différencier les services VTC classiques des services de partage

#### 2. **pdp_routes** (nouvelle)
- Définit les itinéraires (lignes de transport)
- Colonnes principales:
  - `name`: Nom de l'itinéraire
  - `type`: COMMUNAL ou INTER_COMMUNAL
  - `status`: PROPOSED, VOTING, APPROVED, REJECTED
  - `base_price_per_segment`: Prix de base entre deux arrêts
  - `detour_price_per_km`: Prix au km pour détour porte-à-porte
  - `max_detour_communal` / `max_detour_intercommunal`: Distances maximales de détour

#### 3. **pdp_stops** (modifiée)
- Ajout de `pdp_route_id` et `order`
- Permet d'associer les arrêts à un itinéraire avec un ordre défini

#### 3.5. **pdp_route_segments** (nouvelle)
- Stocke les prix fixes de chaque segment entre deux arrêts consécutifs
- Colonnes principales:
  - `pdp_route_id`: Itinéraire concerné
  - `from_stop_id` / `to_stop_id`: Arrêts de départ et d'arrivée du segment
  - `order`: Ordre du segment dans l'itinéraire
  - `price`: Prix fixe du segment (base: 200 FCFA)
  - `distance_km`: Distance du segment
  - `commune`: Commune du segment

#### 4. **pdp_route_votes** (nouvelle)
- Système de vote DAO pour valider les itinéraires
- Colonnes: `pdp_route_id`, `user_id`, `vote` (YES/NO), `comment`

#### 5. **active_shared_rides** (nouvelle)
- Représente un véhicule actuellement en service
- Colonnes principales:
  - `pdp_route_id`: Itinéraire suivi
  - `provider_id`: Chauffeur
  - `service_type_id`: Type de service (pour calculer les km gratuits)
  - `status`: EN_ROUTE, TERMINATED, CANCELLED
  - `available_seats`: Places disponibles
  - `current_latitude` / `current_longitude`: Position GPS en temps réel
  - `next_stop_id`: Prochain arrêt prévu

#### 6. **ride_bookings** (nouvelle)
- Réservations des passagers
- Colonnes principales:
  - `active_shared_ride_id`: Trajet concerné
  - `user_id`: Passager
  - `start_stop_id` / `end_stop_id`: Arrêts de départ et d'arrivée
  - `seats_booked`: Nombre de places réservées
  - `price`: Prix de base
  - `detour_price`: Prix supplémentaire pour détour
  - `status`: CONFIRMED, BOARDED, COMPLETED, CANCELLED

## API Endpoints

### Provider API (`/api/provider/shared/rides/...`)

#### Démarrer un service partagé
```
POST /api/provider/shared/rides/start
Body: {
  "pdp_route_id": 1,
  "total_seats": 15,
  "service_type_id": 2 (optionnel, utilise celui du provider si non spécifié),
  "vehicle_id": 123 (optionnel)
}
```

#### Mettre à jour la position GPS
```
POST /api/provider/shared/rides/{id}/update-position
Body: {
  "latitude": 5.3364,
  "longitude": -4.0267
}
```

#### Déclarer l'arrivée à un arrêt
```
POST /api/provider/shared/rides/{id}/arrive-at-stop
Body: {
  "stop_id": 5
}
```

#### Terminer un trajet
```
POST /api/provider/shared/rides/{id}/end
```

#### Obtenir le trajet actif
```
GET /api/provider/shared/rides/current
```

### User API (`/api/user/shared/rides/...`)

#### Rechercher les trajets à proximité
```
GET /api/user/shared/rides/nearby?latitude=5.3364&longitude=-4.0267&radius=5&pdp_route_id=1
```

#### Calculer le prix
```
POST /api/user/shared/rides/calculate-price
Body: {
  "pdp_route_id": 1,
  "start_stop_id": 2,
  "end_stop_id": 5,
  "service_type_id": 2 (optionnel, requis pour calculer les km gratuits),
  "seats_booked": 1 (optionnel, défaut: 1),
  "detour_latitude": 5.3400 (optionnel),
  "detour_longitude": -4.0300 (optionnel)
}
```

#### Réserver une place
```
POST /api/user/shared/rides/{rideId}/book
Body: {
  "start_stop_id": 2,
  "end_stop_id": 5,
  "seats_booked": 1,
  "detour_latitude": 5.3400 (optionnel - pour trajet porte-à-porte),
  "detour_longitude": -4.0300 (optionnel - pour trajet porte-à-porte),
  "payment_mode": "CASH"
}
```

**Note :** 
- Si `detour_latitude` et `detour_longitude` sont fournis → Trajet porte-à-porte (avec validation et facturation du détour)
- Si ces champs ne sont pas fournis → Trajet arrêt à arrêt (prix = seulement les segments)

#### Mes réservations
```
GET /api/user/shared/rides/bookings
```

#### Annuler une réservation
```
POST /api/user/shared/rides/bookings/{id}/cancel
```

## Types de Trajets PDP

Le système supporte deux types de trajets :

### 1. Trajet Arrêt à Arrêt (Standard)

Le client monte et descend directement aux arrêts définis de l'itinéraire, sans détour.

**Caractéristiques :**
- Pas de détour : le client se rend à l'arrêt de départ et descend à l'arrêt d'arrivée
- Pas de validation de distance de détour nécessaire
- Pas de facturation supplémentaire
- **Prix = Somme des prix des segments traversés**

**Exemple :**
- Client monte à l'arrêt "Carrefour Vie (B)"
- Client descend à l'arrêt "Carrefour OPERA (D)"
- Segments traversés : B→C, C→D
- Prix = Prix segment B→C + Prix segment C→D

### 2. Trajet Porte-à-Porte (Avec Détour)

Le client demande un service de prise en charge à une adresse personnalisée (détour depuis l'arrêt).

**Caractéristiques :**
- Détour depuis l'arrêt de départ pour récupérer le client
- Validation de la distance aller (doit être ≤ max_detour)
- Facturation du détour (distance aller + retour)
- **Prix = Prix des segments + Prix du détour**

## Calcul du Prix

Le système utilise des **prix fixes par segment** stockés dans la table `pdp_route_segments`. Chaque segment (entre deux arrêts consécutifs) a son propre prix fixe qui varie selon la commune et la distance.

### Formule de calcul

**Prix total = Prix des segments + Prix du détour (si applicable)**

1. **Prix des segments**:
   - Chaque segment est défini entre deux arrêts consécutifs d'un itinéraire
   - Chaque segment a un prix fixe stocké dans `pdp_route_segments.price` (prix de base: 200 FCFA)
   - Le prix peut varier selon:
     - La commune (`pdp_route_segments.commune`)
     - La distance du segment (`pdp_route_segments.distance_km`)
   - Prix segments = Addition de tous les prix des segments entre l'arrêt de départ et l'arrêt d'arrivée

2. **Prix du détour** (si porte-à-porte):
   - `distance_aller`: Arrêt de départ → Position client
   - `distance_retour`: Position client → Arrêt de départ
   - `distance_totale = distance_aller + distance_retour`
   - Prix détour = `distance_totale × service_type.price_per_km` (défaut: 200 FCFA/km)

3. **Application des km gratuits** (si applicable):
   - `free_km_per_passenger`: Km gratuits par passager (défini dans `service_type`)
   - `total_free_km = free_km_per_passenger × seats_booked`
   - `total_distance = distance_segments + distance_détour`
   - Si `total_distance ≤ total_free_km` → Trajet gratuit
   - Sinon → Réduction proportionnelle : `réduction = (total_free_km / total_distance) × prix_total`
   - **Prix final = Prix segments + Prix détour - Réduction km gratuits**

### Validation et facturation du porte-à-porte

Pour les trajets porte-à-porte (avec détour), le système valide et facture le détour:

1. **Validation de la distance de détour**:
   - Le système calcule **uniquement la distance ALLER** (arrêt de départ → position client)
   - Distance maximale autorisée selon le type d'itinéraire:
     - Communal: `pdp_routes.max_detour_communal` km (défaut: 5 km)
     - Inter-communal: `pdp_routes.max_detour_intercommunal` km (défaut: 10 km)
   - Si `distance_aller > max_detour`, la réservation est **REFUSÉE** (même si le retour est court)

2. **Facturation du détour**:
   - Si la validation est OK, le système calcule **tous les km réellement parcourus**:
     - `distance_aller`: Arrêt de départ → Position client
     - `distance_retour`: Position client → Arrêt de départ
     - `distance_totale_facturable = distance_aller + distance_retour`
   - Prix du détour: `distance_totale × service_type.price_per_km` (défaut: 200 FCFA/km)

3. **Validation du temps d'attente**:
   - Utilise `pdp_stops.max_waiting_time` pour valider le temps d'attente maximum à l'arrêt

**Prix total facturé = Prix des segments + Prix du détour**

**Exemple 1 - Trajet Arrêt à Arrêt (Standard):**
- Type : Arrêt à arrêt (pas de détour)
- Arrêt de départ: Arrêt #2 "Carrefour Vie"
- Arrêt d'arrivée: Arrêt #5 "Carrefour OPERA"
- Segments traversés:
  - Segment 2→3: 200 FCFA (Commune Cocody, 1.2 km)
  - Segment 3→4: 200 FCFA (Commune Cocody, 1.5 km)
  - Segment 4→5: 100 FCFA (Commune Cocody, 0.8 km)
- Distance totale: 3.5 km
- `free_km_per_passenger`: 2 km
- `seats_booked`: 1
- Total km gratuits: 2 km
- **Prix segments**: 200 + 200 + 100 = 500 FCFA
- **Réduction km gratuits**: (2 / 3.5) × 500 = 285.71 FCFA
- **Prix détour**: 0 FCFA (pas de détour)
- **Prix total**: 500 - 285.71 = 214.29 FCFA

**Cas d'usage :** Le client se rend à l'arrêt "Carrefour Vie", monte dans le véhicule, et descend à l'arrêt "Carrefour OPERA". Aucun détour nécessaire. Les km gratuits réduisent le prix.

**Exemple 2 - Trajet avec détour (ACCEPTÉ):**
- Arrêt de départ: Arrêt #2
- Distance segments: 3.5 km
- Distance aller pour récupérer client: 2 km
- Distance retour à l'itinéraire: 1 km
- Distance totale: 3.5 + 2 + 1 = 6.5 km
- max_detour: 5 km
- service_type.price_per_km: 200 FCFA/km
- `free_km_per_passenger`: 2 km
- `seats_booked`: 1
- Total km gratuits: 2 km
- **Validation**: 2 km ≤ 5 km → ✅ ACCEPTÉ
- **Prix segments**: 750 FCFA
- **Facturation détour**: (2 + 1) × 200 = 600 FCFA
- **Prix avant réduction**: 750 + 600 = 1350 FCFA
- **Réduction km gratuits**: (2 / 6.5) × 1350 = 415.38 FCFA
- **Prix total**: 1350 - 415.38 = 934.62 FCFA

**Exemple 3 - Trajet avec détour (REFUSÉ):**
- Distance aller: 6.2 km
- max_detour: 5 km
- **Validation**: 6.2 km > 5 km → ❌ REFUSÉ
- Même si le retour est court, la réservation est refusée

**Note:** Si les segments ne sont pas définis dans `pdp_route_segments`, le système utilise le prix de base (200 FCFA) × nombre de segments comme fallback.

## Modèles Eloquent

- `App\PdpRoute`: Itinéraires
- `App\PdpRouteSegment`: Segments avec prix fixes
- `App\PdpRouteVote`: Votes sur les itinéraires
- `App\ActiveSharedRide`: Trajets actifs
- `App\RideBooking`: Réservations
- `App\PdpStop`: Arrêts (mis à jour avec relations)

## Contrôleurs

- `App\Http\Controllers\ProviderResources\SharedRideController`: Gestion des trajets côté provider
- `App\Http\Controllers\UserSharedRideController`: Recherche et réservation côté user

## Prochaines Étapes

### Intégrations en cours

1. **Installer les dépendances Web3**
   - Installer `web3.php` ou `ethereum-php` pour les interactions blockchain
   - Commandes : `composer require sc0vu/web3.php` ou `composer require ethereum-php/ethereum-php`

2. **Configurer les variables .env**
   - Voir `docs/integration-summary.md` pour la liste complète des variables
   - Configurer les URLs RPC, adresses de contrats, clés API Mobile Money

3. **Développer les contrats intelligents**
   - Créer le contrat Token ECO (ERC-20) avec Solidity
   - Créer le contrat DAO (Gouvernance) avec Solidity
   - Déployer sur testnet (Polygon Mumbai recommandé)
   - Tester les interactions avec les contrats

4. **Implémenter les APIs réelles des fournisseurs Mobile Money**
   - Compléter l'intégration Orange Money API
   - Compléter l'intégration MTN Mobile Money API
   - Compléter l'intégration Moov Money API (si disponible)
   - Implémenter les webhooks pour les notifications

5. **Tester et déployer**
   - Tests unitaires pour tous les services
   - Tests d'intégration end-to-end
   - Tests de sécurité (signatures, validations)
   - Déploiement en production avec monitoring

**Voir `docs/dao-token-mobile-money-integration-plan.md` pour le plan complet d'intégration DAO, Token ECO et Mobile Money.**

