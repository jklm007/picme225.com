# Plan d'Implémentation : Affichage Itinéraire & Arrêts Dynamiques

## 1. Analyse de la Demande
L'utilisateur souhaite une interface (popup ou page) permettant de :
1.  Visualiser l'itinéraire (segments, arrêts) entre un point de départ et d'arrivée.
2.  Calculer le temps de départ/arrivée en tenant compte du trafic.
3.  Sélectionner un arrêt spécifique.
4.  Voir les véhicules proches pour :
    *   Du "Porte-à-Porte" dynamique (Shared Ride).
    *   Ou rejoindre l'arrêt le plus proche.

## 2. Architecture Technique

### A. Backend (Laravel)
Nous devons enrichir `UserSharedController` pour fournir ces données structurées.

*   **Nouvel Endpoint** : `GET /api/user/shared/routes/search`
    *   **Input** : `s_lat`, `s_lng`, `d_lat`, `d_lng`, `time` (optional).
    *   **Logic** :
        1.  Trouver les `PdpRoute` qui passent à proximité du départ et de l'arrivée (rayon X km).
        2.  Pour chaque route trouvée, identifier le `start_stop` (le plus proche du départ) et le `end_stop` (le plus proche de l'arrivée).
        3.  Calculer le temps de trajet estimé (Google Maps API avec `traffic_model=best_guess`).
        4.  Lister les `ActiveSharedRide` (véhicules en route) sur ces lignes.
    *   **Output** : Liste d'itinéraires avec segments, arrêts, véhicules proches, et ETA.

### B. Frontend (Blade/JS)
Une interface modale ou une nouvelle vue "Trip Planner".

*   **Composants** :
    *   **Map Interactive** : Affiche le tracé de la ligne, les arrêts (marqueurs), et la position user.
    *   **Timeline** : Liste verticale des arrêts avec heures de passage estimées.
    *   **Sélecteur de Mode** : "Rejoindre l'arrêt (Marcher)" vs "Me chercher (Feeder/Taxi)".

## 3. Modifications à Apporter

### Fichier : `UserSharedController.php`
Ajouter la méthode `searchRoutes` qui :
1.  Utilise `PdpStop` pour trouver les arrêts proches.
2.  Reconstruit le chemin entre les arrêts.
3.  Interroge `ActiveSharedRide` pour la position des bus.

### Fichier : `routes/api.php`
Ajouter la route `GET /user/shared/routes/search`.

### Fichier : `resources/views/user/dashboard.blade.php` (ou nouveau fichier)
Ajouter le script JS pour gérer l'affichage de la modale d'itinéraire.

---

## 4. Implémentation (Étape par Étape)

### Étape 1 : Controller Logic
```php
public function searchRoutes(Request $request) {
    // 1. Trouver arrêts proches départ/arrivée
    // 2. Trouver routes connectant ces arrêts
    // 3. Estimer trafic et temps
    // 4. Retourner JSON structuré
}
```

### Étape 2 : Frontend Display
Créer une vue partielle `resources/views/user/include/trip_planner_modal.blade.php` qui contient la map et la liste.

### Étape 3 : Intégration
Lier le formulaire de recherche "Shared Ride" du dashboard à cette nouvelle logique.
