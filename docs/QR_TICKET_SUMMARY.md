# Résumé de l'Implémentation : Ticket QR & Dispatch Hybride

## 1. Composants Livrés

### Base de Données
- **Migrations** : `database/migrations/2025_11_24_000001_create_tickets_and_logs_tables.php`
  - Table `tickets` : Stockage sécurisé des tokens et signatures.
  - Table `ticket_validation_logs` : Audit trail des scans.
  - Table `driver_assignment_logs` : Historique des assignations hybrides.

### Backend (Laravel)
- **Services** :
  - `App\Services\TicketService.php` : Cœur de la sécurité (Génération HMAC, Validation, Expiration).
  - `App\Services\DispatcherHybridService.php` : Logique d'assignation (Manuel vs Broadcast).
- **Controllers** :
  - `App\Http\Controllers\Api\TicketController.php` : Endpoint `/api/scan-ticket`.
  - `App\Http\Controllers\Dispatcher\HybridAssignmentController.php` : Endpoints pour le dispatcher.
- **Models** : `Ticket`, `TicketValidationLog`, `DriverAssignmentLog`.
- **Routes** : Ajoutées à `routes/api.php`.

### Frontend & Mobile
- **Flutter/Android** : `docs/FLUTTER_ANDROID_IMPLEMENTATION.md` (Code pour scanner et afficher QR).
- **Dashboard Dispatcher** : `docs/DISPATCHER_DASHBOARD_SNIPPETS.md` (Code HTML/JS pour les modals).

## 2. Flux de Fonctionnement

### A. Création de Ticket
1. Une course intercommunale est acceptée (`STARTED` ou `ACCEPTED`).
2. Le système appelle `TicketService::generate($request)`.
3. Un token unique et une signature HMAC sont créés.
4. Le QR Code est disponible via `/api/tickets/{request_id}`.

### B. Validation (Scan)
1. Le chauffeur scanne le QR Code avec l'app Driver.
2. Appel à `POST /api/scan-ticket`.
3. Le serveur vérifie :
   - La signature (Anti-falsification).
   - L'expiration (TTL).
   - Le statut (Anti-double dépense).
4. Si valide, le statut passe à `VALIDATED` et un log est créé.

### C. Dispatch Hybride
1. **Manuel** : Le dispatcher choisit un chauffeur précis -> `POST /api/dispatcher/assign-driver`.
2. **Broadcast** : Le dispatcher lance un appel -> `POST /api/dispatcher/broadcast-drivers`. Le système notifie tous les chauffeurs dans le rayon donné.

## 3. Sécurité Mise en Place
- **Signature HMAC-SHA256** : Empêche la modification du ticket par un tiers.
- **Token Unique (UUID)** : Empêche la devinette des IDs.
- **Expiration (TTL)** : Les tickets ne sont valides que 24h (configurable).
- **Logs d'Audit** : Chaque tentative de scan (réussie ou échouée) est enregistrée avec IP et User Agent.

## 4. Prochaines Étapes
1. Exécuter la migration : `php artisan migrate`.
2. Intégrer les snippets Flutter dans l'application Driver et Passager.
3. Intégrer les snippets HTML/JS dans le panel Dispatcher existant.
4. Configurer `TICKET_SECRET` dans le fichier `.env` (sinon `APP_KEY` est utilisé par défaut).
