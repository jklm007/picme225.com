# Structure JSON pour Import d'Itinéraires PDP

## Format Requis

Le fichier JSON doit contenir un tableau d'objets représentant les itinéraires. Chaque itinéraire doit avoir la structure suivante :

```json
[
  {
    "name": "LIGNE 1 EXEMPLE",
    "description": "Description de la ligne",
    "type": "COMMUNAL",
    "status": "APPROVED",
    "stops": [
      {
        "name": "Nom de l'arrêt",
        "address": "Adresse complète",
        "latitude": 5.346746,
        "longitude": -3.995813,
        "type": "gare",
        "order": 1
      }
    ],
    "segments": [
      {
        "from_stop_order": 1,
        "to_stop_order": 2,
        "price": 200,
        "distance_km": 1.2
      }
    ]
  }
]
```

## Champs Obligatoires

### Route (Itinéraire)
- **name** (string) : Nom de la ligne (ex: "LIGNE 1 COCODY - ANGRE")
- **description** (string, optionnel) : Description détaillée
- **type** (enum) : "COMMUNAL" ou "INTER_COMMUNAL"
- **status** (enum) : "PROPOSED", "VOTING", "APPROVED", "REJECTED"
- **stops** (array) : Liste des arrêts
- **segments** (array) : Liste des segments entre arrêts

### Stop (Arrêt)
- **name** (string) : Nom de l'arrêt
- **address** (string) : Adresse complète
- **latitude** (float) : Coordonnée GPS latitude
- **longitude** (float) : Coordonnée GPS longitude
- **type** (enum) : "gare" (point de départ) ou "arret" (arrêt intermédiaire)
- **order** (integer) : Ordre séquentiel (1, 2, 3...)

### Segment
- **from_stop_order** (integer) : Numéro d'ordre de l'arrêt de départ
- **to_stop_order** (integer) : Numéro d'ordre de l'arrêt d'arrivée
- **price** (integer) : Prix en FCFA pour ce segment
- **distance_km** (float) : Distance en kilomètres

## Règles de Validation

1. Les **order** des arrêts doivent être séquentiels et commencer à 1
2. Les **from_stop_order** et **to_stop_order** doivent correspondre aux **order** des arrêts existants
3. Le premier arrêt doit avoir **type** = "gare"
4. Les coordonnées GPS doivent être valides pour Abidjan (latitude ~5.3, longitude ~-4.0)
5. Les prix doivent être en multiples de 100 FCFA (200, 300, 400...)

## Catégorisation Automatique

Les itinéraires sont automatiquement catégorisés selon leur distance totale :
- **Standard** : Distance totale ≤ 20 km
- **Voyage** : Distance totale > 20 km

## Exemple Complet

Voir le fichier `complete_official_routes.json` dans `database/seeders/` pour un exemple complet avec 12 lignes.

## Utilisation

1. Accédez à l'interface admin : `/admin/pdp/routes/import`
2. Sélectionnez votre fichier JSON
3. Cochez "Mettre à jour les itinéraires existants" si vous voulez écraser les lignes existantes
4. Cliquez sur "Importer"

Les itinéraires seront immédiatement disponibles dans l'application mobile.
