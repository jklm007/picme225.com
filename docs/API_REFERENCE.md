# 📡 API Reference - Picme225.com

## 🎯 Guide de Référence Rapide

**Base URL**: `https://api.picme225.com/api`  
**Authentication**: Bearer Token (OAuth2)  
**Format**: JSON

---

## 🔐 Authentication

### Inscription
```http
POST /signup
Content-Type: application/json

{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "mobile": "0707070707",
  "country_code": "+225"
}

Response 201:
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer",
  "expires_in": 31536000,
  "user": { ... }
}
```

### Connexion
```http
POST /signin
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123",
  "device_type": "android",
  "device_token": "fcm_token_here"
}

Response 200:
{
  "access_token": "...",
  "user": { ... }
}
```

### Connexion Sociale
```http
POST /auth/google
Content-Type: application/json

{
  "access_token": "google_access_token",
  "device_type": "ios",
  "device_token": "apns_token"
}

POST /auth/facebook
// Same structure
```

---

## 👤 Profil Utilisateur

### Obtenir le Profil
```http
GET /details
Authorization: Bearer {token}

Response 200:
{
  "id": 1,
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "mobile": "0707070707",
  "picture": "https://...",
  "wallet_balance": 5000,
  "eco_token_balance": 150.5,
  "wallet_address": "0x123...",
  "rating": 4.8,
  "currency": "XOF"
}
```

### Mettre à Jour le Profil
```http
POST /update/profile
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "first_name": "John",
  "last_name": "Doe",
  "mobile": "0707070707",
  "picture": <file>
}
```

### Changer le Mot de Passe
```http
POST /change/password
Authorization: Bearer {token}

{
  "old_password": "old123",
  "password": "new123",
  "password_confirmation": "new123"
}
```

---

## 🚗 Services de Transport

### Liste des Services
```http
GET /services
Authorization: Bearer {token}

Response 200:
[
  {
    "id": 1,
    "name": "Economy",
    "provider_name": "Sedan",
    "image": "https://...",
    "capacity": 4,
    "fixed": 500,
    "price": 150,
    "minute": 50,
    "distance": 10,
    "calculator": "MIN",
    "description": "Affordable rides"
  },
  ...
]
```

### Types de Services
```http
GET /service-types
Authorization: Bearer {token}

Query Parameters:
- latitude: 5.3600
- longitude: -4.0083

Response 200:
{
  "service_types": [
    {
      "id": 1,
      "name": "Standard",
      "fixed": 500,
      "price_per_km": 150,
      "price_per_minute": 50,
      "capacity": 4,
      "available_providers": 12
    }
  ]
}
```

---

## 🚕 Réservation de Course

### Estimation de Tarif
```http
GET /estimated/fare
Authorization: Bearer {token}

Query Parameters:
- s_latitude: 5.3600
- s_longitude: -4.0083
- d_latitude: 5.3700
- d_longitude: -4.0183
- service_type: 1

Response 200:
{
  "estimated_fare": 2500,
  "distance": 5.2,
  "time": 15,
  "base_price": 500,
  "distance_price": 780,
  "time_price": 750,
  "tax": 470,
  "surge_multiplier": 1.0,
  "currency": "XOF"
}
```

### Envoyer une Demande
```http
POST /send/request
Authorization: Bearer {token}

{
  "service_type": 1,
  "s_latitude": 5.3600,
  "s_longitude": -4.0083,
  "s_address": "Cocody, Abidjan",
  "d_latitude": 5.3700,
  "d_longitude": -4.0183,
  "d_address": "Plateau, Abidjan",
  "payment_mode": "WALLET",
  "use_wallet": 1,
  "promocode_id": null,
  "schedule_date": null,
  "schedule_time": null
}

Response 200:
{
  "message": "New request created!",
  "request_id": 123,
  "current_provider": null
}
```

### Vérifier le Statut
```http
GET /request/check
Authorization: Bearer {token}

Response 200:
{
  "status": "ACCEPTED",
  "request": {
    "id": 123,
    "booking_id": "PM123456",
    "status": "ACCEPTED",
    "provider": {
      "id": 45,
      "first_name": "Pierre",
      "last_name": "Kouassi",
      "mobile": "0707070707",
      "picture": "https://...",
      "rating": 4.9,
      "latitude": 5.3610,
      "longitude": -4.0090
    },
    "service_type": {
      "name": "Economy",
      "image": "https://..."
    },
    "estimated_fare": 2500,
    "distance": 5.2
  }
}
```

### Annuler une Course
```http
POST /cancel/request
Authorization: Bearer {token}

{
  "request_id": 123,
  "cancel_reason": "Change of plans"
}

Response 200:
{
  "message": "Request cancelled successfully"
}
```

---

## 🤝 Service Partagé

### Rechercher Trajets Disponibles
```http
GET /shared/rides/nearby
Authorization: Bearer {token}

Query Parameters:
- latitude: 5.3600
- longitude: -4.0083
- radius: 5000 (meters)
- service_type_id: 2

Response 200:
{
  "rides": [
    {
      "id": 10,
      "provider": {
        "name": "Jean Kouadio",
        "rating": 4.8,
        "vehicle": "Toyota Corolla"
      },
      "route": {
        "id": 1,
        "name": "Cocody - Plateau",
        "stops": [...]
      },
      "current_location": {
        "latitude": 5.3605,
        "longitude": -4.0085
      },
      "available_seats": 2,
      "current_segment": 2,
      "estimated_arrival": "2025-11-23T15:30:00Z",
      "distance_to_pickup": 0.5
    }
  ]
}
```

### Calculer le Prix
```http
POST /shared/rides/calculate-price
Authorization: Bearer {token}

{
  "ride_id": 10,
  "start_stop_id": 3,
  "end_stop_id": 7,
  "seats_booked": 1,
  "detour_latitude": null,
  "detour_longitude": null
}

Response 200:
{
  "price": 800,
  "breakdown": {
    "segment_price": 800,
    "detour_price": 0,
    "total_price": 800
  },
  "segments": [
    {
      "from": "Cocody Centre",
      "to": "Angré",
      "price": 200
    },
    ...
  ]
}
```

### Réserver une Place
```http
POST /shared/rides/{rideId}/book
Authorization: Bearer {token}

{
  "start_stop_id": 3,
  "end_stop_id": 7,
  "seats_booked": 1,
  "payment_mode": "WALLET",
  "detour_latitude": null,
  "detour_longitude": null,
  "detour_address": null
}

Response 201:
{
  "message": "Booking successful",
  "booking": {
    "id": 456,
    "booking_code": "SH456789",
    "price": 800,
    "status": "CONFIRMED",
    "pickup_stop": "Cocody Centre",
    "dropoff_stop": "Angré"
  }
}
```

### Mes Réservations
```http
GET /shared/rides/bookings
Authorization: Bearer {token}

Query Parameters:
- status: CONFIRMED|COMPLETED|CANCELLED

Response 200:
{
  "bookings": [
    {
      "id": 456,
      "booking_code": "SH456789",
      "ride": {...},
      "price": 800,
      "status": "CONFIRMED",
      "created_at": "2025-11-23T14:00:00Z"
    }
  ]
}
```

### Annuler une Réservation
```http
POST /shared/rides/bookings/{id}/cancel
Authorization: Bearer {token}

Response 200:
{
  "message": "Booking cancelled successfully",
  "refund_amount": 800
}
```

---

## 💰 Paiements

### Ajouter de l'Argent au Wallet
```http
POST /add/money
Authorization: Bearer {token}

{
  "amount": 10000,
  "payment_mode": "CARD",
  "card_id": 5
}

Response 200:
{
  "message": "Money added successfully",
  "wallet_balance": 15000
}
```

### Historique Wallet
```http
GET /wallet/passbook
Authorization: Bearer {token}

Response 200:
{
  "wallet_balance": 15000,
  "wallet_transations": [
    {
      "id": 100,
      "amount": 10000,
      "status": "CREDITED",
      "type": "ADD_MONEY",
      "created_at": "2025-11-23T14:00:00Z"
    },
    {
      "id": 99,
      "amount": -2500,
      "status": "DEBITED",
      "type": "RIDE_PAYMENT",
      "created_at": "2025-11-23T13:00:00Z"
    }
  ]
}
```

---

## 💳 Gestion des Cartes

### Ajouter une Carte
```http
POST /card
Authorization: Bearer {token}

{
  "stripe_token": "tok_visa_123456"
}

Response 201:
{
  "message": "Card added successfully",
  "card": {
    "id": 5,
    "last_four": "4242",
    "brand": "Visa",
    "is_default": 1
  }
}
```

### Liste des Cartes
```http
GET /card
Authorization: Bearer {token}

Response 200:
{
  "cards": [
    {
      "id": 5,
      "last_four": "4242",
      "brand": "Visa",
      "is_default": 1
    }
  ]
}
```

### Supprimer une Carte
```http
DELETE /card/{id}
Authorization: Bearer {token}

Response 200:
{
  "message": "Card deleted successfully"
}
```

---

## 📱 Mobile Money

### Initier un Paiement
```http
POST /mobile-money/payment/initiate
Authorization: Bearer {token}

{
  "amount": 5000,
  "phone_number": "0707070707",
  "provider": "orange",
  "reference": "WALLET_RECHARGE",
  "type": "WALLET_RECHARGE"
}

Response 201:
{
  "message": "Payment initiated successfully",
  "transaction": {
    "id": 789,
    "transaction_id": "MM789456123",
    "status": "PENDING",
    "amount": 5000,
    "provider": "orange"
  }
}
```

### Vérifier une Transaction
```http
GET /mobile-money/payment/verify/{transactionId}
Authorization: Bearer {token}

Response 200:
{
  "transaction": {
    "id": 789,
    "transaction_id": "MM789456123",
    "status": "SUCCESS",
    "amount": 5000,
    "processed_at": "2025-11-23T14:05:00Z"
  },
  "status": "SUCCESS"
}
```

### Historique Mobile Money
```http
GET /mobile-money/transactions
Authorization: Bearer {token}

Query Parameters:
- provider: orange|mtn|moov
- status: PENDING|SUCCESS|FAILED

Response 200:
{
  "data": [
    {
      "id": 789,
      "transaction_id": "MM789456123",
      "provider": "orange",
      "amount": 5000,
      "status": "SUCCESS",
      "type": "WALLET_RECHARGE",
      "created_at": "2025-11-23T14:00:00Z"
    }
  ]
}
```

---

## 🪙 EcoToken

### Solde de Tokens
```http
GET /eco-token/balance
Authorization: Bearer {token}

Response 200:
{
  "balance": 150.5,
  "blockchain_balance": 150.5,
  "wallet_address": "0x123456789abcdef..."
}
```

### Historique des Transactions
```http
GET /eco-token/transactions
Authorization: Bearer {token}

Response 200:
{
  "data": [
    {
      "id": 50,
      "type": "REWARD",
      "amount": 10.5,
      "transaction_hash": "0xabc...",
      "status": "CONFIRMED",
      "created_at": "2025-11-23T14:00:00Z"
    }
  ]
}
```

### Transférer des Tokens
```http
POST /eco-token/transfer
Authorization: Bearer {token}

{
  "to_wallet_address": "0x987654321...",
  "amount": 50.0
}

Response 200:
{
  "message": "Transfer initiated",
  "transaction_hash": "0xdef456..."
}
```

### Payer avec des Tokens
```http
POST /eco-token/pay
Authorization: Bearer {token}

{
  "amount": 25.0,
  "reference_type": "RIDE",
  "reference_id": 123
}

Response 200:
{
  "message": "Payment successful",
  "transaction_hash": "0xghi789..."
}
```

---

## 🏛️ DAO (Gouvernance)

### Liste des Propositions
```http
GET /dao/proposals
Authorization: Bearer {token}

Query Parameters:
- status: ACTIVE|PASSED|REJECTED|EXECUTED
- type: PRICE_CHANGE|ROUTE_ADDITION|...

Response 200:
{
  "data": [
    {
      "id": 5,
      "title": "Reduce base fare for shared rides",
      "description": "...",
      "type": "PRICE_CHANGE",
      "status": "ACTIVE",
      "votes_for": 1500,
      "votes_against": 300,
      "votes_abstain": 100,
      "start_time": "2025-11-20T00:00:00Z",
      "end_time": "2025-11-27T00:00:00Z",
      "proposer": {
        "name": "Alice Koné"
      }
    }
  ]
}
```

### Détails d'une Proposition
```http
GET /dao/proposals/{id}
Authorization: Bearer {token}

Response 200:
{
  "id": 5,
  "title": "...",
  "description": "...",
  "type": "PRICE_CHANGE",
  "status": "ACTIVE",
  "execution_data": {
    "service_type_id": 2,
    "new_base_price": 400
  },
  "votes_for": 1500,
  "votes_against": 300,
  "total_votes": 1900,
  "votes": [
    {
      "user": "Bob Traoré",
      "vote": "FOR",
      "token_amount": 100,
      "created_at": "..."
    }
  ]
}
```

### Créer une Proposition
```http
POST /dao/proposals
Authorization: Bearer {token}

{
  "type": "PRICE_CHANGE",
  "title": "Reduce base fare for shared rides",
  "description": "To encourage more users to use shared rides...",
  "execution_data": {
    "service_type_id": 2,
    "new_base_price": 400
  },
  "voting_period_days": 7
}

Response 201:
{
  "message": "Proposal created successfully",
  "proposal": {
    "id": 6,
    "blockchain_proposal_id": "0xabc123...",
    ...
  }
}
```

### Voter sur une Proposition
```http
POST /dao/proposals/{id}/vote
Authorization: Bearer {token}

{
  "vote": "FOR"
}

Response 201:
{
  "message": "Vote registered successfully",
  "vote": {
    "id": 100,
    "vote": "FOR",
    "token_amount": 150.5,
    "transaction_hash": "0xdef456..."
  }
}
```

---

## 🎫 Codes Promo

### Liste des Promos Disponibles
```http
GET /promocodes
Authorization: Bearer {token}

Response 200:
{
  "promocodes": [
    {
      "id": 10,
      "promo_code": "WELCOME50",
      "discount": 50,
      "discount_type": "PERCENTAGE",
      "expiration": "2025-12-31",
      "usage_count": 0,
      "max_usage": 1
    }
  ]
}
```

### Appliquer un Code Promo
```http
POST /promocode/add
Authorization: Bearer {token}

{
  "promocode": "WELCOME50"
}

Response 200:
{
  "message": "Promocode applied successfully",
  "promocode": {
    "id": 10,
    "discount": 50,
    "discount_type": "PERCENTAGE"
  }
}
```

### Historique des Promos
```http
GET /promo/passbook
Authorization: Bearer {token}

Response 200:
{
  "promos": [
    {
      "id": 1,
      "promo_code": "WELCOME50",
      "amount": 1250,
      "status": "USED",
      "used_at": "2025-11-23T14:00:00Z"
    }
  ]
}
```

---

## 📊 Historique

### Trajets Passés
```http
GET /trips
Authorization: Bearer {token}

Response 200:
{
  "trips": [
    {
      "id": 123,
      "booking_id": "PM123456",
      "provider": {
        "name": "Pierre Kouassi",
        "picture": "...",
        "mobile": "0707070707"
      },
      "service_type": "Economy",
      "s_address": "Cocody",
      "d_address": "Plateau",
      "distance": 5.2,
      "payment": 2500,
      "status": "COMPLETED",
      "created_at": "2025-11-23T10:00:00Z",
      "user_rated": 1,
      "provider_rated": 1
    }
  ]
}
```

### Détails d'un Trajet
```http
GET /trip/details
Authorization: Bearer {token}

Query Parameters:
- request_id: 123

Response 200:
{
  "request": {
    "id": 123,
    "booking_id": "PM123456",
    "provider": {...},
    "service_type": {...},
    "payment": {
      "total": 2500,
      "base_price": 500,
      "distance_price": 780,
      "time_price": 750,
      "tax": 470,
      "payment_mode": "WALLET"
    },
    "rating": {
      "rating": 5,
      "comment": "Excellent service!"
    },
    "map_image": "https://maps.googleapis.com/..."
  }
}
```

### Trajets à Venir
```http
GET /upcoming/trips
Authorization: Bearer {token}

Response 200:
{
  "trips": [
    {
      "id": 124,
      "booking_id": "PM124567",
      "schedule_at": "2025-11-24T08:00:00Z",
      "s_address": "Cocody",
      "d_address": "Aéroport",
      "estimated_fare": 5000,
      "status": "SCHEDULED"
    }
  ]
}
```

---

## 📢 Campagnes Publicitaires

### Liste des Campagnes
```http
GET /ad-campaigns
Authorization: Bearer {token}

Query Parameters:
- status: DRAFT|ACTIVE|PAUSED|COMPLETED

Response 200:
{
  "data": [
    {
      "id": 1,
      "name": "Summer Promotion",
      "status": "ACTIVE",
      "budget": 100000,
      "spent": 45000,
      "platforms": ["facebook", "google"],
      "start_date": "2025-11-20",
      "end_date": "2025-12-20"
    }
  ]
}
```

### Créer une Campagne
```http
POST /ad-campaigns
Authorization: Bearer {token}

{
  "name": "Winter Campaign",
  "objective": "CONVERSIONS",
  "budget": 50000,
  "start_date": "2025-12-01",
  "end_date": "2025-12-31",
  "platforms": ["facebook", "tiktok"],
  "target_audience": {
    "age_min": 18,
    "age_max": 45,
    "locations": ["Abidjan"],
    "interests": ["transport", "technology"]
  }
}

Response 201:
{
  "message": "Campaign created successfully",
  "campaign": {
    "id": 2,
    "name": "Winter Campaign",
    "status": "DRAFT"
  }
}
```

### Générer du Contenu IA
```http
POST /ad-campaigns/generate-content
Authorization: Bearer {token}

{
  "campaign_id": 2,
  "template_id": 5,
  "platform": "facebook",
  "prompt": "Create an engaging ad for affordable rides"
}

Response 200:
{
  "content": {
    "headline": "Voyagez Malin avec Picme225!",
    "description": "Économisez jusqu'à 50% sur vos trajets...",
    "call_to_action": "Réserver Maintenant",
    "image_suggestions": [...]
  }
}
```

### Publier une Campagne
```http
POST /ad-campaigns/{id}/publish
Authorization: Bearer {token}

{
  "platforms": ["facebook", "google"]
}

Response 200:
{
  "message": "Campaign published successfully",
  "platform_ids": {
    "facebook": "fb_campaign_123",
    "google": "google_campaign_456"
  }
}
```

### Performances d'une Campagne
```http
GET /ad-campaigns/{id}/performance
Authorization: Bearer {token}

Response 200:
{
  "campaign": {
    "id": 1,
    "name": "Summer Promotion"
  },
  "performance": {
    "impressions": 150000,
    "clicks": 7500,
    "conversions": 450,
    "ctr": 5.0,
    "cpc": 6.0,
    "cost_per_conversion": 100,
    "total_spent": 45000
  },
  "by_platform": [
    {
      "platform": "facebook",
      "impressions": 100000,
      "clicks": 5000,
      "conversions": 300
    }
  ]
}
```

---

## 📍 Lieux Favoris

### Liste des Lieux
```http
GET /location
Authorization: Bearer {token}

Response 200:
{
  "data": [
    {
      "id": 1,
      "type": "HOME",
      "address": "Cocody, Abidjan",
      "latitude": 5.3600,
      "longitude": -4.0083
    },
    {
      "id": 2,
      "type": "WORK",
      "address": "Plateau, Abidjan",
      "latitude": 5.3200,
      "longitude": -4.0250
    }
  ]
}
```

### Ajouter un Lieu
```http
POST /location
Authorization: Bearer {token}

{
  "type": "HOME",
  "address": "Cocody, Abidjan",
  "latitude": 5.3600,
  "longitude": -4.0083
}

Response 201:
{
  "message": "Location added successfully",
  "location": {
    "id": 3,
    "type": "HOME",
    "address": "Cocody, Abidjan"
  }
}
```

---

## 🔔 Notifications

### Envoyer un Token FCM
```http
POST /update/profile
Authorization: Bearer {token}

{
  "device_type": "android",
  "device_token": "fcm_token_here"
}
```

---

## 📝 Codes d'Erreur

### Codes HTTP
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

### Format d'Erreur
```json
{
  "error": "Error message",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

---

## 🔒 Rate Limiting

- **Limite**: 60 requêtes par minute par utilisateur
- **Header**: `X-RateLimit-Limit`, `X-RateLimit-Remaining`
- **Réponse 429**: Too Many Requests

---

## 🌍 Internationalisation

### Header de Langue
```http
Accept-Language: fr
```

**Langues supportées**: `fr`, `en`

---

## 📚 Ressources Complémentaires

- **Documentation complète**: `/docs/ANALYSE_FONCTIONNALITES.md`
- **Architecture**: `/docs/ARCHITECTURE_OVERVIEW.md`
- **Postman Collection**: À venir
- **Swagger/OpenAPI**: À venir

---

**Version API**: v1  
**Dernière mise à jour**: 23 Novembre 2025
