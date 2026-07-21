# Intégration Android – Service Partage

1. **Formulaire de création**
   - Ajouter un onglet « Partage » dans `MainActivity` / `HomeFragment`.
   - Champs : départ, destination, nombre de sièges, option PDP (liste alimentée par `/api/user/pdp-stops`), segments dynamiques.
   - Appeler `POST /api/user/shared/fare` pour afficher l’estimation avant validation.

2. **Demande de course**
   - Valider les coordonnées saisies puis appeler `POST /api/user/shared/request`.
   - Sauvegarder l’`request_id` pour rafraîchir l’état (polling `/api/user/shared/route/{id}` et `/api/user/shared/drivers/{id}`).

3. **Suivi en temps réel**
   - Afficher la liste de chauffeurs compatibles (réponse de `/drivers`) avec badge direction/détour.
   - Montrer les segments sur la carte (polylines issues du JSON `segments`).

4. **Gestion des passagers**
   - Boutons « Ajouter » / « Supprimer » qui appellent respectivement
     `POST /api/user/shared/add-passenger/{id}` et
     `DELETE /api/user/shared/remove-passenger/{id}/{passengerId}`.

5. **Notifications**
   - Traiter les push `request_accepted` pour afficher la carte chauffeur + itinéraire PDP.
   - Lorsqu’un chauffeur accepte, rafraîchir la course et verrouiller l’ajout de passagers si `status` ≠ `MATCHING`.

6. **Résumé historique**
   - Dans `HistoryActivity`, afficher les segments JSON et le tarif partagé (`fare.fare_per_passenger`).

