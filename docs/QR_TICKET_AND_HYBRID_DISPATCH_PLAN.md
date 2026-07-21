# Plan d'Implémentation : Ticket QR Sécurisé & Dispatch Hybride

## 1. Analyse de l'existant

### Structure Actuelle
- **UserRequests** : Table centrale gérant les courses. Contient `provider_id`, `status` (SEARCHING, ACCEPTED, STARTED, etc.), `service_type_id`.
- **DispatcherController** : Gère actuellement l'interface dispatcher. Il faudra ajouter les méthodes pour l'assignation hybride.
- **ServiceType** : Contient le flag `is_intercommunal` (ajouté récemment). C'est le déclencheur pour la génération de tickets.

### Manquements Identifiés
- Pas de table pour stocker les tickets QR.
- Pas de logique de signature HMAC.
- Pas de distinction claire entre "Assignation Manuelle" et "Broadcast Dispatcher" dans le code actuel (le broadcast est souvent automatique via `UserApiController`).
- Pas de logs d'audit pour les validations de tickets.

## 2. Architecture Technique

### A. Base de Données

#### 1. `tickets`
| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint | PK |
| ride_id | bigint | FK -> user_requests |
| user_id | bigint | FK -> users |
| token | string | Identifiant unique (ULID/UUID) |
| signature | string | HMAC-SHA256 signature |
| status | enum | PENDING, VALIDATED, EXPIRED, CANCELLED |
| expires_at | timestamp | Date d'expiration |
| validated_at | timestamp | Date de validation |
| validated_by_type | string | driver, dispatcher, admin |
| validated_by_id | bigint | ID du validateur |

#### 2. `ticket_validation_logs`
Pour l'audit trail complet (tentatives échouées, double scan, etc.).

#### 3. `driver_assignment_logs`
Pour tracer qui a assigné qui (Dispatcher ID -> Driver ID).

### B. Flux de Données

1. **Création de la course (Intercommunal)**
   - User ou Dispatcher crée la request.
   - Status: `PENDING` ou `SEARCHING`.

2. **Assignation (Mode Hybride)**
   - **Manuel** : Dispatcher sélectionne un Driver -> Update `user_requests` -> Status `ACCEPTED` -> Trigger Event `RideAssigned`.
   - **Automatique** : Dispatcher clique "Broadcast" -> Système cherche drivers (Rayon X km) -> Envoie notif -> Premier qui accepte -> Status `ACCEPTED`.

3. **Génération Ticket**
   - Une fois `status` = `ACCEPTED` (ou `CONFIRMED`) ET `service_type.is_intercommunal` = true :
   - Appel `TicketService::generate(ride_id)`.
   - Stockage DB.
   - Envoi au User (App).

4. **Validation**
   - Driver scanne QR.
   - API `POST /api/scan-ticket` reçue.
   - `TicketService::validate(token, signature)`.
   - Si OK : Update Ticket Status -> Update Ride Status (si nécessaire, ex: `PICKED_UP`) -> Notif User.

## 3. Sécurité

- **HMAC-SHA256** : `hash_hmac('sha256', $ride_id . $user_id . $token . $expires_at, env('TICKET_SECRET'))`.
- **TTL** : Le ticket expire après X heures (configurable, ex: 24h).
- **Idempotence** : Un ticket ne peut être validé qu'une fois.

## 4. Composants à Créer

### Backend (Laravel)
- **Migrations** : `tickets`, `ticket_logs`.
- **Models** : `Ticket`, `TicketValidationLog`.
- **Services** : 
    - `TicketService` (Generation, Validation).
    - `DispatcherHybridService` (Logique d'assignation).
- **Controllers** : 
    - `Api/TicketController` (Scan).
    - `Dispatcher/HybridAssignmentController` (Assignation).
- **Events** : `TicketGenerated`, `TicketValidated`, `DispatcherBroadcast`.

### Frontend (Mobile/Web)
- **Android** : Écran "Mon Ticket" (QR Display), Scanner QR (Driver).
- **Web (Dispatcher)** : Interface d'assignation (Liste drivers + Bouton Broadcast), Modal de validation manuelle.

## 5. Plan d'exécution
1. Créer les migrations.
2. Implémenter `TicketService` (Cœur sécurité).
3. Implémenter les API Endpoints.
4. Implémenter la logique Dispatcher Hybride.
5. Tests.
