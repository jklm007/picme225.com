# Configuration des Variantes de Course par Service

## 📋 Configuration Actuelle

| Service | Variantes Autorisées | Logique de Prix |
|---------|---------------------|-----------------|
| **Standard** | Privé | Prix/km standard |
| **Taxi** | Privé | Prix/km taxi |
| **Share-Ride** | Arrêt, Partage | Prix segmentés (Arrêt) ou prix partagé (Partage) |
| **Voyage** | Privé, Arrêt | Prix/km standard (Privé) ou -25% (Arrêt) |
| **Delivery** | Privé | Prix/km delivery |

## 🎯 Logique Métier

### **Standard & Taxi**
- **Uniquement "Privé"** : Transport individuel porte-à-porte
- Pas d'option "Arrêt" car incompatible avec le concept de taxi privé

### **Share-Ride (Wôrô-wôrô)**
- **"Arrêt"** : Itinéraires fixes avec prix segmentés
  - Utilise les lignes PDP configurées
  - Prix calculé selon les segments parcourus
- **"Partage"** : Covoiturage sur itinéraire libre
  - Prix partagé entre passagers
  - Détours autorisés selon configuration

### **Voyage**
- **"Privé"** : Course longue distance exclusive
- **"Arrêt"** : Accepte d'être déposé à un arrêt fixe
  - Réduction de 25% sur le prix/km
  - Encourage l'utilisation des gares routières

## 🔧 Modification de la Configuration

### Via Base de Données
```sql
UPDATE service_types 
SET allowed_variants = '["prive", "arret"]',
    arret_discount_percent = 20.00
WHERE name LIKE '%Standard%';
```

### Via Seeder
Modifier `ConfigureServiceVariantsSeeder.php` et relancer :
```bash
php artisan db:seed --class=Database\Seeders\ConfigureServiceVariantsSeeder
```

## 📱 Impact sur l'Application Mobile

L'application Android doit :
1. Récupérer `allowed_variants` depuis l'API
2. Afficher uniquement les variantes autorisées pour le service sélectionné
3. Masquer les options non disponibles

## 💡 Recommandations

### Pour ajouter un nouveau service :
1. Créer le service dans `service_types`
2. Définir `allowed_variants` : `["prive"]`, `["arret"]`, ou `["prive", "arret", "partage"]`
3. Si "Arrêt" autorisé, définir `arret_discount_percent` (optionnel)

### Règles de cohérence :
- **Transport individuel** → `["prive"]` uniquement
- **Transport collectif** → `["arret", "partage"]`
- **Transport mixte** → `["prive", "arret"]` avec réduction

## 🚀 Prochaines Étapes

1. ✅ Migration appliquée
2. ✅ Configuration initiale effectuée
3. ⏳ Modifier l'API pour retourner `allowed_variants`
4. ⏳ Adapter l'app Android pour filtrer les variantes
5. ⏳ Tester chaque combinaison service/variante
