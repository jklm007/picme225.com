# Documentation des Fonctionnalités Backend & Guide d'Intégration Mobile

Ce document recense l'ensemble des fonctionnalités disponibles sur le serveur Picme225, leurs endpoints API correspondants, et fournit des directives claires pour le développement ou la mise à jour des applications mobiles (Android/Flutter).

---

## 1. Authentification & Profil Utilisateur

### Fonctionnalités
- Inscription / Connexion (Email, Mobile, Social).
- Vérification OTP.
- Gestion du profil (Mot de passe, Localisation).
- Portefeuille (Wallet) et Historique.

### Endpoints Clés
| Méthode | Endpoint | Description |
| :--- | :--- | :--- |
| POST | `/api/signup` | Inscription utilisateur |
| POST | `/api/signin` | Connexion (retourne Bearer Token) |
| POST | `/api/auth/facebook` | Connexion Facebook |
| POST | `/api/auth/google` | Connexion Google |
| POST | `/api/verify` | Vérification OTP |
| GET | `/api/details` | Détails du profil utilisateur |
| POST | `/api/update/profile` | Mise à jour du profil |
| POST | `/api/change/password` | Changement de mot de passe |
| GET | `/api/wallet/passbook` | Historique du portefeuille |

### Guidelines Mobile
- **Token Management** : Stockez le `access_token` de manière sécurisée (ex: `flutter_secure_storage`). Il doit être envoyé dans le header `Authorization: Bearer <token>` pour toutes les routes protégées.
- **Social Login** : Utilisez les SDK natifs (Google Sign-In, Facebook Login) pour obtenir le token d'accès, puis envoyez-le au backend via `/api/auth/{provider}`.

---

## 2. Gestion des Courses (VTC & Partage)

### Fonctionnalités
- Estimation de prix (VTC standard, Location, Partage).
- Création de demande de course.
- Suivi en temps réel (Status Check).
- Historique des courses.
- Annulation et Notation.

### Endpoints Clés
| Méthode | Endpoint | Description |
| :--- | :--- | :--- |
| GET | `/api/services` | Liste des types de services disponibles |
| GET | `/api/estimated/fare` | Estimation prix (VTC classique) |
| POST | `/api/send/request` | Créer une course |
| POST | `/api/cancel/request` | Annuler une course |
| GET | `/api/request/check` | Polling statut course (CRITIQUE) |
| GET | `/api/trips` | Historique des courses passées |
| POST | `/api/rate/provider` | Noter le chauffeur |

### Guidelines Mobile
- **Polling** : L'application doit appeler `/api/request/check` régulièrement (ex: toutes les 5s) lorsqu'une course est en cours pour mettre à jour l'interface (Recherche -> Accepté -> En route -> Terminé).
- **Maps** : Intégrez Google Maps SDK pour afficher l'itinéraire (Polyline décodée depuis `route_key` dans la réponse).

---

## 3. Covoiturage Intercommunal (Nouveau)

### Fonctionnalités
- Recherche de trajets partagés à proximité.
- Réservation de siège.
- Calcul de prix dynamique (Logique Km Gratuits).
- Points de rassemblement (PDP Stops).

### Endpoints Clés
| Méthode | Endpoint | Description |
| :--- | :--- | :--- |
| GET | `/api/pdp-stops` | Liste des points de rassemblement |
| GET | `/api/shared/rides/nearby` | Trouver des trajets partagés |
| POST | `/api/shared/rides/calculate-price` | Estimer prix partage |
| POST | `/api/shared/rides/{rideId}/book` | Réserver une place |
| GET | `/api/shared/rides/bookings` | Mes réservations |

### Guidelines Mobile
- **Affichage** : Affichez les `pdp-stops` sur la carte comme des marqueurs distincts.
- **Logique Prix** : Le backend gère la logique "Gratuit si < X km", affichez simplement le prix retourné par l'API.

---

## 4. Ticket QR Sécurisé (Nouveau)

### Fonctionnalités
- Génération de QR Code pour validation de présence.
- Scan par le chauffeur.

### Endpoints Clés
| Méthode | Endpoint | Description |
| :--- | :--- | :--- |
| GET | `/api/tickets/{request_id}` | Récupérer le token/signature du ticket |
| POST | `/api/scan-ticket` | (Driver App) Valider un ticket |

### Guidelines Mobile
- **Passager** : Utilisez un package QR (ex: `qr_flutter`) pour afficher les données JSON reçues (`token`, `signature`).
- **Chauffeur** : Utilisez un scanner (ex: `mobile_scanner`) pour lire le QR et envoyer le payload brut à l'endpoint de scan.

---

## 5. DAO & Gouvernance (Nouveau)

### Fonctionnalités
- Propositions de vote (Changement prix, nouvelles routes).
- Vote avec Tokens ECO.

### Endpoints Clés
| Méthode | Endpoint | Description |
| :--- | :--- | :--- |
| GET | `/api/dao/proposals` | Liste des propositions |
| POST | `/api/dao/proposals` | Créer une proposition |
| POST | `/api/dao/proposals/{id}/vote` | Voter (FOR, AGAINST, ABSTAIN) |

### Guidelines Mobile
- **UI** : Créez une section "Communauté" ou "Gouvernance".
- **Feedback** : Affichez clairement le statut de la proposition (Active, Terminée) et les résultats en temps réel.

---

## 6. Token ECO & Paiements (Nouveau)

### Fonctionnalités
- Solde Token ECO.
- Transfert P2P.
- Paiement Mobile Money (Orange, MTN, Moov).

### Endpoints Clés
| Méthode | Endpoint | Description |
| :--- | :--- | :--- |
| GET | `/api/eco-token/balance` | Solde ECO |
| POST | `/api/eco-token/transfer` | Envoyer des tokens |
| POST | `/api/mobile-money/payment/initiate` | Recharger via Mobile Money |
| GET | `/api/mobile-money/payment/verify/{id}` | Vérifier statut recharge |

### Guidelines Mobile
- **Mobile Money** : Le flux est asynchrone. Après initiation, affichez un loader et pollez l'endpoint de vérification jusqu'à succès ou échec (ou attendez le webhook via socket si implémenté).
- **Sécurité** : Ne stockez jamais les clés privées ou mnémoniques sur le téléphone. Toutes les actions blockchain sensibles passent par l'API backend qui détient les droits (Custodial Wallet).

---

## 7. Publicité (Ad Campaigns)

### Fonctionnalités
- Affichage de campagnes pub.
- Génération de contenu IA.

### Endpoints Clés
| Méthode | Endpoint | Description |
| :--- | :--- | :--- |
| GET | `/api/ad-campaigns` | Liste des campagnes actives |

### Guidelines Mobile
- Intégrez les bannières publicitaires aux endroits stratégiques (ex: écran de recherche de chauffeur) sans obstruer l'UX principale.

---

## Résumé Technique pour le Développeur Mobile

1.  **Base URL** : `https://picme225.com` (ou URL de staging).
2.  **Headers Standards** :
    ```json
    {
      "Content-Type": "application/json",
      "Accept": "application/json",
      "Authorization": "Bearer {YOUR_TOKEN}"
    }
    ```
3.  **Gestion des Erreurs** :
    - `401 Unauthorized` : Token expiré -> Rediriger vers Login ou Refresh Token.
    - `422 Unprocessable Entity` : Erreur de validation formulaire (afficher les messages d'erreur).
    - `500 Server Error` : Afficher un message générique "Erreur serveur, réessayez plus tard".

Ce document sert de référence unique pour aligner le développement mobile avec les capacités actuelles du backend.
