# Documentation API PDP & PostGIS

Cette documentation explique comment utiliser l'API Hybride PDP (Point de Prise en Charge) utilisant PostGIS et Photon.

## Endpoints Disponibles

### 1. `GET /pdp/communes`
**Description** : Retourne la liste de toutes les communes actives avec leur centre géographique. (Le polygone n'est pas retourné pour éviter d'alourdir la réponse).
**Réponse** :
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "ville": "Abidjan",
      "commune": "Cocody",
      "latitude_centre": 5.3854,
      "longitude_centre": -3.9922,
      "statut": "actif"
    }
  ]
}
```

### 2. `GET /pdp/communes/{id}/arrets`
**Description** : Retourne tous les arrêts validés pour une commune donnée.

### 3. `GET /pdp/nearby`
**Description** : Trouve les arrêts les plus proches d'une coordonnée GPS (utilise le moteur de calcul spatial ultra-rapide GiST de PostGIS).
**Query Parameters** :
- `lat` (Requis) : Latitude de l'utilisateur
- `lng` (Requis) : Longitude de l'utilisateur
- `radius` (Optionnel) : Rayon de recherche en km (Défaut : 2 km)

### 4. `GET /pdp/search`
**Description** : Moteur de recherche principal pour l'utilisateur. Cherche d'abord dans la base de données locale. Simultanément, interroge Photon pour proposer de nouveaux résultats si le lieu n'est pas en base de données.
**Query Parameters** :
- `q` (Requis) : La requête texte (ex: "Carrefour Angré")
- `commune_id` (Optionnel) : L'ID de la commune pour restreindre ou augmenter le score de confiance.

**Le "Score de Confiance" (PhotonGeocodingService)**
Chaque suggestion retournée par Photon reçoit un score sur 100 :
- +30 si le nom correspond partiellement ou totalement.
- +40 si PostGIS (`ST_Contains`) confirme que la coordonnée Photon tombe physiquement dans le polygone de la commune demandée.
- +30 si le type OpenStreetMap est très précis (`node`).
- -50 si le lieu est physiquement en dehors de la commune demandée.
- -20 si un arrêt PDP existe déjà à moins de 100 mètres (risque de doublon).

Si un utilisateur choisit un point Photon avec un score >= 90, il peut être enregistré automatiquement. Sinon, il nécessitera une vérification (`en_attente`).

### 5. `POST /pdp/create`
**Description** : Permet de créer manuellement un PDP ou de sauvegarder un point Photon.
**Body** :
```json
{
  "nom_arret": "Pharmacie des Allées",
  "type_arret": "commerce",
  "commune_id": 1,
  "latitude": 5.3444,
  "longitude": -4.0111,
  "source_coordonnees": "photon",
  "confidence_score": 95
}
```

### 6. `PUT /pdp/{id}/correct`
**Description** : Permet à un administrateur ou super-utilisateur de corriger la latitude/longitude d'un arrêt PDP qui est imprécis. Ceci regénèrera l'objet Spatial POINT dans PostgreSQL.
