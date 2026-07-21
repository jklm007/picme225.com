# 🏗️ Architecture Overview - Picme225.com

## 📋 Résumé Exécutif

**Picme225.com** est une plateforme de mobilité intelligente qui révolutionne le transport urbain en Côte d'Ivoire en combinant:

- 🚗 **Services VTC traditionnels** avec géolocalisation en temps réel
- 🤝 **Covoiturage intelligent** (instantané et planifié)
- 🪙 **Économie tokenisée** avec EcoToken blockchain
- 🏛️ **Gouvernance décentralisée** via DAO
- 💳 **Paiements flexibles** (Mobile Money, Crypto, Cartes)
- 🤖 **Marketing IA** pour campagnes publicitaires automatisées

---

## 🎯 Architecture Globale

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENTS                                   │
├─────────────┬─────────────┬─────────────┬──────────────────────┤
│  Mobile App │  Mobile App │  Web Admin  │   Web Dispatcher     │
│   (Users)   │ (Providers) │   Panel     │      Panel           │
└──────┬──────┴──────┬──────┴──────┬──────┴──────┬───────────────┘
       │             │             │             │
       │             │             │             │
       └─────────────┴─────────────┴─────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│                    API GATEWAY (Laravel)                         │
│                  Laravel Passport (OAuth2)                       │
└─────────────────────────────────────────────────────────────────┘
                         │
         ┌───────────────┼───────────────┐
         │               │               │
         ▼               ▼               ▼
┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   CORE      │  │  BLOCKCHAIN │  │  EXTERNAL   │
│  SERVICES   │  │   SERVICES  │  │   SERVICES  │
└─────────────┘  └─────────────┘  └─────────────┘
```

---

## 🧩 Modules Principaux

### 1. Core Transport Module
```
┌──────────────────────────────────────────┐
│         TRANSPORT SERVICES               │
├──────────────────────────────────────────┤
│                                          │
│  ┌────────────┐      ┌────────────┐     │
│  │  Standard  │      │   Shared   │     │
│  │   Rides    │      │   Rides    │     │
│  └────────────┘      └────────────┘     │
│                                          │
│  ┌────────────┐      ┌────────────┐     │
│  │  Rental    │      │ Ambulance  │     │
│  │  Services  │      │  Services  │     │
│  └────────────┘      └────────────┘     │
│                                          │
└──────────────────────────────────────────┘
         │
         ▼
┌──────────────────────────────────────────┐
│      MATCHING ENGINE                     │
├──────────────────────────────────────────┤
│  • Geo-matching                          │
│  • Availability check                    │
│  • Price calculation                     │
│  • Route optimization                    │
└──────────────────────────────────────────┘
```

### 2. Shared Ride Module
```
┌──────────────────────────────────────────┐
│       SHARED RIDE SYSTEM                 │
├──────────────────────────────────────────┤
│                                          │
│  ┌────────────────────────────────┐     │
│  │   Instant Shared Rides         │     │
│  │   (ActiveSharedRide)           │     │
│  │                                │     │
│  │   • Real-time matching         │     │
│  │   • Dynamic pricing            │     │
│  │   • Door-to-door detours       │     │
│  │   • Multi-passenger support    │     │
│  └────────────────────────────────┘     │
│                                          │
│  ┌────────────────────────────────┐     │
│  │   Planned Routes (PDP)         │     │
│  │   (PdpRoute, PdpStop)          │     │
│  │                                │     │
│  │   • Fixed routes & stops       │     │
│  │   • Scheduled departures       │     │
│  │   • Segment-based pricing      │     │
│  │   • Community voting           │     │
│  └────────────────────────────────┘     │
│                                          │
└──────────────────────────────────────────┘
```

### 3. Payment Module
```
┌──────────────────────────────────────────┐
│        PAYMENT ORCHESTRATOR              │
├──────────────────────────────────────────┤
│                                          │
│  ┌──────────┐  ┌──────────┐  ┌────────┐ │
│  │  Cash    │  │  Wallet  │  │ Cards  │ │
│  └──────────┘  └──────────┘  └────────┘ │
│                                          │
│  ┌──────────┐  ┌──────────┐  ┌────────┐ │
│  │  Mobile  │  │   Eco    │  │ Promo  │ │
│  │  Money   │  │  Token   │  │ Codes  │ │
│  └──────────┘  └──────────┘  └────────┘ │
│                                          │
└──────────────────────────────────────────┘
         │
         ▼
┌──────────────────────────────────────────┐
│      PAYMENT PROCESSORS                  │
├──────────────────────────────────────────┤
│  • Stripe (Cards)                        │
│  • Orange Money API                      │
│  • MTN Mobile Money API                  │
│  • Moov Money API                        │
│  • Web3 (EcoToken)                       │
└──────────────────────────────────────────┘
```

### 4. Blockchain Module
```
┌──────────────────────────────────────────┐
│         BLOCKCHAIN LAYER                 │
├──────────────────────────────────────────┤
│                                          │
│  ┌────────────────────────────────┐     │
│  │      EcoToken Contract         │     │
│  │      (ERC-20)                  │     │
│  │                                │     │
│  │  • Transfer                    │     │
│  │  • Mint (rewards)              │     │
│  │  • Burn (payments)             │     │
│  │  • Balance tracking            │     │
│  └────────────────────────────────┘     │
│                                          │
│  ┌────────────────────────────────┐     │
│  │      DAO Contract              │     │
│  │      (Governance)              │     │
│  │                                │     │
│  │  • Create proposals            │     │
│  │  • Vote with tokens            │     │
│  │  • Execute proposals           │     │
│  │  • Timelock                    │     │
│  └────────────────────────────────┘     │
│                                          │
└──────────────────────────────────────────┘
         │
         ▼
┌──────────────────────────────────────────┐
│      Web3 Provider                       │
│      (Ethereum / BSC)                    │
└──────────────────────────────────────────┘
```

### 5. AI Marketing Module
```
┌──────────────────────────────────────────┐
│         AI AD PLATFORM                   │
├──────────────────────────────────────────┤
│                                          │
│  ┌────────────────────────────────┐     │
│  │   Content Generation           │     │
│  │   (AiAdService)                │     │
│  │                                │     │
│  │  • Text generation             │     │
│  │  • Image suggestions           │     │
│  │  • A/B testing                 │     │
│  │  • Performance optimization    │     │
│  └────────────────────────────────┘     │
│                                          │
│  ┌────────────────────────────────┐     │
│  │   Multi-Platform Publisher     │     │
│  │   (AdPlatformService)          │     │
│  │                                │     │
│  │  • Facebook Ads API            │     │
│  │  • Google Ads API              │     │
│  │  • TikTok Ads API              │     │
│  │  • Performance tracking        │     │
│  └────────────────────────────────┘     │
│                                          │
└──────────────────────────────────────────┘
```

---

## 🔄 Flux de Données Principaux

### Flux 1: Réservation de Course Standard
```
User App                API                 Matching Engine         Provider App
   │                     │                         │                      │
   │──1. Request Ride──▶│                         │                      │
   │                     │──2. Calculate Price───▶│                      │
   │                     │◀──3. Price Quote────────│                      │
   │◀──4. Show Price────│                         │                      │
   │                     │                         │                      │
   │──5. Confirm────────▶│                         │                      │
   │                     │──6. Find Providers────▶│                      │
   │                     │                         │──7. Notify──────────▶│
   │                     │                         │                      │
   │                     │                         │◀──8. Accept──────────│
   │                     │◀──9. Provider Found─────│                      │
   │◀─10. Matched───────│                         │                      │
   │                     │                         │                      │
   │◀────────11. Real-time Updates (Socket.io)──────────────────────────▶│
   │                     │                         │                      │
   │──12. Rate──────────▶│                         │                      │
   │                     │──13. Process Payment───▶│                      │
   │◀─14. Receipt───────│                         │                      │
```

### Flux 2: Réservation Trajet Partagé
```
User App                API              Shared Ride Service      Provider App
   │                     │                         │                      │
   │──1. Search Nearby──▶│                         │                      │
   │                     │──2. Query Active────────▶│                      │
   │                     │   Rides                 │                      │
   │◀──3. Available──────│◀────────────────────────│                      │
   │   Rides             │                         │                      │
   │                     │                         │                      │
   │──4. Calculate───────▶│                         │                      │
   │   Price             │──5. Compute Segments───▶│                      │
   │◀──5. Price Quote────│◀────────────────────────│                      │
   │                     │                         │                      │
   │──6. Book Seat──────▶│                         │                      │
   │                     │──7. Reserve Seat────────▶│                      │
   │                     │──8. Notify Provider─────────────────────────▶│
   │◀──9. Confirmed─────│◀────────────────────────│                      │
   │                     │                         │                      │
   │◀────────10. Live Tracking (Socket.io)──────────────────────────────▶│
```

### Flux 3: Vote DAO
```
User App              API              Web3Service         Blockchain
   │                   │                     │                  │
   │──1. Create────────▶│                     │                  │
   │   Proposal        │──2. Validate────────▶│                  │
   │                   │   Token Balance     │                  │
   │                   │                     │──3. Create───────▶│
   │                   │                     │   Proposal       │
   │                   │                     │◀──4. Tx Hash─────│
   │◀──5. Confirmed────│◀────────────────────│                  │
   │                   │                     │                  │
   │──6. Vote FOR──────▶│                     │                  │
   │                   │──7. Lock Tokens─────▶│                  │
   │                   │                     │──8. Submit Vote──▶│
   │                   │                     │◀──9. Confirmed───│
   │◀─10. Vote Saved───│◀────────────────────│                  │
   │                   │                     │                  │
   │                   │   [After voting period]                │
   │                   │                     │──11. Execute─────▶│
   │                   │                     │    Proposal      │
```

### Flux 4: Paiement Mobile Money
```
User App           API          MobileMoneyService    Provider API
   │                │                   │                  │
   │──1. Initiate───▶│                   │                  │
   │   Payment      │──2. Create────────▶│                  │
   │                │   Transaction     │──3. API Call────▶│
   │                │                   │◀──4. Pending─────│
   │◀──5. Prompt────│◀──────────────────│                  │
   │   Received     │                   │                  │
   │                │                   │                  │
   │──6. Confirm────┐                   │                  │
   │   on Phone     │                   │                  │
   │                │                   │◀──5. Webhook─────│
   │                │◀──6. Update───────│   Notification   │
   │                │   Status          │                  │
   │◀──7. Success───│                   │                  │
```

---

## 🗄️ Architecture de Base de Données

### Schéma Relationnel Simplifié
```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│    Users    │         │  Providers   │         │   Admins    │
├─────────────┤         ├──────────────┤         ├─────────────┤
│ id          │         │ id           │         │ id          │
│ email       │         │ email        │         │ email       │
│ wallet_addr │         │ fleet_id     │         │ role        │
│ eco_balance │         │ latitude     │         └─────────────┘
└──────┬──────┘         │ longitude    │
       │                └──────┬───────┘
       │                       │
       │                       │
       ▼                       ▼
┌─────────────────────────────────────┐
│        UserRequests                 │
├─────────────────────────────────────┤
│ id                                  │
│ user_id          (FK → users)       │
│ provider_id      (FK → providers)   │
│ service_type_id  (FK → service_types)│
│ s_latitude, s_longitude             │
│ d_latitude, d_longitude             │
│ status (SEARCHING, ACCEPTED, etc.)  │
│ payment_mode                        │
└─────────────────────────────────────┘
       │
       ├──────────────┬──────────────┬──────────────┐
       ▼              ▼              ▼              ▼
┌─────────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────┐
│  Payments   │ │ Ratings  │ │Passengers│ │RequestFilters│
└─────────────┘ └──────────┘ └──────────┘ └──────────────┘

┌──────────────────┐         ┌─────────────────┐
│ActiveSharedRides │         │   PdpRoutes     │
├──────────────────┤         ├─────────────────┤
│ id               │         │ id              │
│ provider_id      │         │ name            │
│ pdp_route_id     │         │ description     │
│ available_seats  │         │ is_active       │
│ current_segment  │         └────────┬────────┘
└──────────────────┘                  │
       │                              │
       ▼                              ▼
┌──────────────────┐         ┌─────────────────┐
│  RideBookings    │         │PdpRouteSegments │
├──────────────────┤         ├─────────────────┤
│ id               │         │ id              │
│ user_id          │         │ route_id        │
│ ride_id          │         │ start_stop_id   │
│ seats_booked     │         │ end_stop_id     │
│ price            │         │ price           │
└──────────────────┘         └─────────────────┘

┌──────────────────┐         ┌─────────────────┐
│  DaoProposals    │         │EcoTokenTransact │
├──────────────────┤         ├─────────────────┤
│ id               │         │ id              │
│ user_id          │         │ user_id         │
│ blockchain_id    │         │ type            │
│ type             │         │ amount          │
│ status           │         │ tx_hash         │
│ votes_for        │         │ status          │
│ votes_against    │         └─────────────────┘
└──────────────────┘
       │
       ▼
┌──────────────────┐
│    DaoVotes      │
├──────────────────┤
│ id               │
│ proposal_id      │
│ user_id          │
│ vote (FOR/AGAINST)│
│ token_amount     │
└──────────────────┘
```

---

## 🔐 Sécurité & Authentification

### Multi-Guard Authentication
```
┌─────────────────────────────────────────────────────────┐
│              Laravel Passport (OAuth2)                  │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────┐ │
│  │   User   │  │ Provider │  │  Admin   │  │ Fleet  │ │
│  │  Guard   │  │  Guard   │  │  Guard   │  │ Guard  │ │
│  └──────────┘  └──────────┘  └──────────┘  └────────┘ │
│                                                         │
│  ┌──────────┐  ┌──────────┐                            │
│  │Dispatcher│  │ Account  │                            │
│  │  Guard   │  │  Guard   │                            │
│  └──────────┘  └──────────┘                            │
│                                                         │
└─────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────┐
│              Token Management                           │
├─────────────────────────────────────────────────────────┤
│  • Access Tokens (JWT)                                  │
│  • Refresh Tokens                                       │
│  • Token Expiration (configurable)                      │
│  • Token Revocation                                     │
│  • Scopes & Permissions                                 │
└─────────────────────────────────────────────────────────┘
```

### Sécurité des Paiements
```
┌─────────────────────────────────────────────────────────┐
│              Payment Security Layers                    │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Layer 1: SSL/TLS Encryption                           │
│  ─────────────────────────────────────────────────     │
│  • HTTPS only                                          │
│  • Certificate pinning (mobile)                        │
│                                                         │
│  Layer 2: PCI-DSS Compliance                           │
│  ─────────────────────────────────────────────────     │
│  • No card storage (tokenization via Stripe)           │
│  • 3D Secure support                                   │
│                                                         │
│  Layer 3: Webhook Verification                         │
│  ─────────────────────────────────────────────────     │
│  • HMAC signature validation                           │
│  • Replay attack prevention                            │
│                                                         │
│  Layer 4: Blockchain Security                          │
│  ─────────────────────────────────────────────────     │
│  • Private key management                              │
│  • Transaction signing                                 │
│  • Gas limit protection                                │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 📡 Communication en Temps Réel

### Socket.io Architecture
```
┌─────────────────────────────────────────────────────────┐
│                  Socket.io Server                       │
│                  (Node.js/Express)                      │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Namespaces:                                           │
│  ┌──────────────────────────────────────────────┐     │
│  │  /user         - User events                 │     │
│  │  /provider     - Provider events             │     │
│  │  /dispatcher   - Dispatcher events           │     │
│  │  /admin        - Admin events                │     │
│  └──────────────────────────────────────────────┘     │
│                                                         │
│  Events:                                               │
│  • location-update    - GPS tracking                   │
│  • ride-status        - Status changes                 │
│  • new-request        - New ride request               │
│  • chat-message       - In-ride chat                   │
│  • notification       - Push notifications             │
│                                                         │
└─────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────┐
│                  Redis Pub/Sub                          │
│              (For horizontal scaling)                   │
└─────────────────────────────────────────────────────────┘
```

---

## 🚀 Déploiement & Infrastructure

### Production Architecture
```
┌─────────────────────────────────────────────────────────┐
│                    Load Balancer                        │
│                    (Nginx/HAProxy)                      │
└────────────────────┬────────────────────────────────────┘
                     │
         ┌───────────┼───────────┐
         │           │           │
         ▼           ▼           ▼
    ┌────────┐  ┌────────┐  ┌────────┐
    │  Web   │  │  Web   │  │  Web   │
    │Server 1│  │Server 2│  │Server 3│
    │(Laravel)│  │(Laravel)│  │(Laravel)│
    └────────┘  └────────┘  └────────┘
         │           │           │
         └───────────┼───────────┘
                     │
         ┌───────────┼───────────┐
         │           │           │
         ▼           ▼           ▼
    ┌────────┐  ┌────────┐  ┌────────┐
    │ MySQL  │  │ Redis  │  │ Queue  │
    │Master/ │  │ Cache  │  │Workers │
    │ Slave  │  │        │  │        │
    └────────┘  └────────┘  └────────┘
         │
         ▼
    ┌────────────────────────────────┐
    │     External Services          │
    ├────────────────────────────────┤
    │  • Firebase (Push)             │
    │  • Stripe (Payments)           │
    │  • Mobile Money APIs           │
    │  • Blockchain Node             │
    │  • Google Maps API             │
    │  • S3/Cloud Storage            │
    └────────────────────────────────┘
```

---

## 📊 Métriques & Monitoring

### KPIs à Surveiller
```
┌─────────────────────────────────────────────────────────┐
│                  Business Metrics                       │
├─────────────────────────────────────────────────────────┤
│  • Daily Active Users (DAU)                            │
│  • Rides per day                                       │
│  • Average ride value                                  │
│  • Provider utilization rate                          │
│  • Customer satisfaction (ratings)                     │
│  • Token circulation                                   │
│  • DAO participation rate                             │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                 Technical Metrics                       │
├─────────────────────────────────────────────────────────┤
│  • API response time (p50, p95, p99)                   │
│  • Database query performance                          │
│  • Cache hit rate                                      │
│  • Queue processing time                               │
│  • Error rate                                          │
│  • Uptime (99.9% target)                              │
│  • Blockchain transaction success rate                │
└─────────────────────────────────────────────────────────┘
```

---

## 🔧 Technologies & Stack

### Backend
- **Framework**: Laravel 10.x
- **Language**: PHP 8.1+
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Queue**: Redis/Beanstalkd
- **Search**: Elasticsearch (optionnel)

### Frontend
- **Web**: Vue.js 2.x, Bootstrap 3
- **Mobile**: React Native / Flutter (à confirmer)
- **Build**: Gulp, Laravel Elixir

### Real-time
- **WebSocket**: Socket.io
- **Server**: Node.js, Express

### Blockchain
- **Network**: Ethereum / Binance Smart Chain
- **Library**: Web3.js, web3.php
- **Wallet**: MetaMask integration

### External APIs
- **Maps**: Google Maps API
- **Payments**: Stripe, Mobile Money APIs
- **Push**: Firebase Cloud Messaging
- **Social**: Facebook, Google OAuth
- **AI**: OpenAI API (pour génération de contenu)

### DevOps
- **Version Control**: Git
- **CI/CD**: GitHub Actions / GitLab CI
- **Containers**: Docker (optionnel)
- **Monitoring**: Laravel Telescope, Sentry
- **Logging**: ELK Stack (optionnel)

---

## 🎯 Roadmap Technique

### Phase 1: Stabilisation (Q1 2026)
- [ ] Tests unitaires complets
- [ ] Tests d'intégration
- [ ] Documentation API (Swagger/OpenAPI)
- [ ] Performance optimization
- [ ] Security audit

### Phase 2: Scalabilité (Q2 2026)
- [ ] Microservices migration
- [ ] Kubernetes deployment
- [ ] CDN integration
- [ ] Database sharding
- [ ] Caching strategy

### Phase 3: Innovation (Q3-Q4 2026)
- [ ] Machine Learning pour pricing
- [ ] Autonomous vehicle integration
- [ ] Advanced DAO features
- [ ] NFT marketplace
- [ ] Cross-chain support

---

## 📚 Ressources & Documentation

### Documentation Interne
- `/docs/API.md` - Documentation API
- `/docs/DEPLOYMENT.md` - Guide de déploiement
- `/docs/CONTRIBUTING.md` - Guide de contribution
- `/docs/ARCHITECTURE.md` - Ce document

### Documentation Externe
- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Passport](https://laravel.com/docs/passport)
- [Socket.io Documentation](https://socket.io/docs)
- [Web3.js Documentation](https://web3js.readthedocs.io)
- [Stripe API](https://stripe.com/docs/api)

---

## 🏁 Conclusion

L'architecture de **Picme225.com** est conçue pour:

✅ **Scalabilité** - Support de milliers d'utilisateurs simultanés  
✅ **Fiabilité** - Haute disponibilité et tolérance aux pannes  
✅ **Sécurité** - Protection des données et des transactions  
✅ **Innovation** - Intégration blockchain et IA  
✅ **Flexibilité** - Architecture modulaire et extensible  

La plateforme combine le meilleur des technologies traditionnelles (Laravel, MySQL) avec des innovations modernes (Blockchain, IA) pour créer une solution de mobilité unique et compétitive.

---

**Document généré le**: 23 Novembre 2025  
**Version**: 1.0  
**Maintenu par**: Équipe Technique Picme225
