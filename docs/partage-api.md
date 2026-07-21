# API Partage

## Endpoints Utilisateur (`/api/user/shared/*`)

### POST `/api/user/shared/request`
Crée une course partagée.

| Champ | Type | Description |
|-------|------|-------------|
| `service_type_id` | int | ID du type de service partage |
| `payment_mode` | string (`CASH`, `CARD`, `PAYPAL`) | Mode de paiement |
| `s_latitude` / `s_longitude` | float | Coordonnées de départ |
| `d_latitude` / `d_longitude` | float | Coordonnées d’arrivée globale |
| `s_address` / `d_address` | string | Libellés des points |
| `grouping_point_id` | int? | ID d’un arrêt PDP |
| `segments` | array | Liste des segments `{s_latitude,s_longitude,d_latitude,d_longitude,s_address?,d_address?,price?,segment_name?,user_id?}` |
| `passenger_ids` | array<int>? | Liste d’autres passagers déjà connus |

**Réponse 201**
```json
{
  "message": "New request created",
  "request_id": 123,
  "fare": {
    "fare_per_passenger": 650,
    "distance_km": 6.5
  }
}
```

### GET `/api/user/shared/route/{request_id}`
Retourne les détails d’une requête (segments enrichis et tarif estimé).

### GET `/api/user/shared/drivers/{request_id}`
Liste des chauffeurs compatibles (direction, distance, détour estimé).

### POST `/api/user/shared/add-passenger/{request_id}`
Ajoute un passager existant.

| Champ | Type | Description |
| `user_id` | int | ID de l’utilisateur à ajouter |
| `baggage_count` | int? | Nombre de bagages déclarés |

### DELETE `/api/user/shared/remove-passenger/{request_id}/{passenger_id}`
Supprime un passager de la requête.

### POST `/api/user/shared/fare`
Estimation rapide du tarif partagé.

| Champ | Type |
| `service_type_id` | int |
| `segments` | array |
| `passenger_count` | int? |

## Endpoints Chauffeur (`/api/provider/shared/*`) – Guard `providerapi`

### POST `/api/provider/shared/accept/{request_id}`
Accepte une course partagée (vérifie la capacité et assigne le chauffeur).

### POST `/api/provider/shared/reject/{request_id}`
Refuse la course et supprime le filtre correspondant.

