# 🔄 Refactoring: Déplacer is_intercommunal de PdpStop vers ServiceType

**Date**: 23 Novembre 2025  
**Problème**: Le paramètre intercommunal est au mauvais endroit  
**Solution**: Déplacer de `pdp_stops` vers `service_types`

---

## 🎯 Analyse du Problème

### ❌ **Situation Actuelle (Incorrecte)**

#### Dans `pdp_stops`:
```php
$table->boolean('is_outstation_hub')->default(false);
```

**Problème**:
- ❌ Le paramètre `is_outstation_hub` (intercommunal) est au niveau de l'arrêt
- ❌ Cela implique qu'un arrêt peut être intercommunal ou non
- ❌ Logique incorrecte: c'est le **SERVICE** qui est intercommunal, pas l'arrêt

### ✅ **Situation Correcte**

#### Dans `service_types`:
```php
$table->boolean('is_intercommunal')->default(false);
```

**Raison**:
- ✅ Un **service** est soit communal (restreint à une commune)
- ✅ Soit intercommunal (traverse plusieurs communes)
- ✅ Les arrêts sont juste des points géographiques avec une commune

---

## 🔍 Logique Métier

### Service Communal
```
Service Type: "Partage Cocody"
- is_intercommunal: false
- commune: "Cocody"
- Arrêts autorisés: Uniquement dans Cocody
```

**Exemple**:
- Arrêt 1: "Angré" (Cocody) ✅
- Arrêt 2: "Riviera" (Cocody) ✅
- Arrêt 3: "Plateau Centre" (Plateau) ❌ Refusé

### Service Intercommunal
```
Service Type: "Partage Abidjan"
- is_intercommunal: true
- commune: null (ou "Abidjan")
- Arrêts autorisés: Toutes les communes
```

**Exemple**:
- Arrêt 1: "Angré" (Cocody) ✅
- Arrêt 2: "Plateau Centre" (Plateau) ✅
- Arrêt 3: "Yopougon Marché" (Yopougon) ✅

---

## 🔧 Corrections à Apporter

### 1. Ajouter le Champ dans `service_types`

**Nouvelle Migration**: `2025_11_23_000001_add_is_intercommunal_to_service_types_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            // Indique si le service traverse plusieurs communes
            $table->boolean('is_intercommunal')
                  ->default(false)
                  ->after('commune')
                  ->comment('Service intercommunal (traverse plusieurs communes)');
        });
    }

    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn('is_intercommunal');
        });
    }
};
```

### 2. Supprimer le Champ de `pdp_stops`

**Migration de Nettoyage**: `2025_11_23_000002_remove_is_outstation_hub_from_pdp_stops.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pdp_stops', function (Blueprint $table) {
            if (Schema::hasColumn('pdp_stops', 'is_outstation_hub')) {
                $table->dropColumn('is_outstation_hub');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pdp_stops', function (Blueprint $table) {
            $table->boolean('is_outstation_hub')->default(false);
        });
    }
};
```

### 3. Mettre à Jour le Modèle `ServiceType`

**Fichier**: `app/ServiceType.php`

```php
protected $fillable = [
    // ... champs existants ...
    'commune',
    'communes',
    'is_intercommunal', // NOUVEAU
];

protected $casts = [
    // ... casts existants ...
    'is_intercommunal' => 'boolean', // NOUVEAU
];
```

### 4. Mettre à Jour le Modèle `PdpStop`

**Fichier**: `app/PdpStop.php`

```php
protected $fillable = [
    'name',
    'address',
    'latitude',
    'longitude',
    'commune',
    // 'is_outstation_hub', // SUPPRIMER
    'usage_count',
    'is_active',
    'description',
    'max_waiting_time',
    'allowed_service_types',
    'priority',
    'is_recommended',
    'pdp_route_id',
    'order',
];

protected $casts = [
    'allowed_service_types' => 'array',
    // 'is_outstation_hub' => 'boolean', // SUPPRIMER
    'is_active' => 'boolean',
    'is_recommended' => 'boolean',
    'order' => 'integer',
];
```

---

## 📝 Mise à Jour des Vues Admin

### Formulaire de Création de Service (`create.blade.php`)

**Ajouter après le champ "communes"**:

```html
<!-- Service Intercommunal -->
<div class="form-group row">
    <label for="is_intercommunal" class="col-xs-12 col-form-label">
        Service Intercommunal
    </label>
    <div class="col-xs-10">
        <div class="form-check">
            <input type="checkbox" 
                   class="form-check-input" 
                   id="is_intercommunal" 
                   name="is_intercommunal" 
                   value="1"
                   {{ old('is_intercommunal') ? 'checked' : '' }}>
            <label class="form-check-label" for="is_intercommunal">
                Ce service traverse plusieurs communes
            </label>
        </div>
        <small class="form-text text-muted">
            <strong>Coché</strong>: Service intercommunal (ex: Cocody → Plateau → Yopougon)<br>
            <strong>Non coché</strong>: Service communal (limité à une seule commune)
        </small>
    </div>
</div>
```

### JavaScript pour Gérer la Logique

```javascript
<script>
$(document).ready(function(){
    // Gérer l'affichage des champs commune/communes selon is_intercommunal
    function toggleCommuneFields() {
        if ($('#is_intercommunal').is(':checked')) {
            // Service intercommunal: désactiver commune unique, activer communes multiples
            $('#commune').prop('disabled', true).val('');
            $('#communes_multi').prop('disabled', false);
            
            // Message d'aide
            $('#commune').closest('.form-group').find('.form-text').html(
                '<em>Désactivé pour les services intercommunaux</em>'
            );
        } else {
            // Service communal: activer commune unique, désactiver communes multiples
            $('#commune').prop('disabled', false);
            $('#communes_multi').prop('disabled', true).val([]);
            
            // Message d'aide
            $('#commune').closest('.form-group').find('.form-text').html(
                'Sélectionnez la commune où ce service est disponible'
            );
        }
    }
    
    // Initialiser au chargement
    toggleCommuneFields();
    
    // Écouter les changements
    $('#is_intercommunal').change(function(){
        toggleCommuneFields();
    });
});
</script>
```

---

## 🔍 Logique de Filtrage dans l'API

### Avant (Incorrect)
```php
// Dans UserApiController@services()
$stops = PdpStop::where('is_outstation_hub', false)
    ->where('commune', $userCommune)
    ->get();
```

### Après (Correct)
```php
// Dans UserApiController@services()
$services = ServiceType::where('status', 1)
    ->where(function($query) use ($userCommune) {
        // Services intercommunaux (toujours disponibles)
        $query->where('is_intercommunal', true)
              // OU services communaux de la commune de l'utilisateur
              ->orWhere(function($subQuery) use ($userCommune) {
                  $subQuery->where('is_intercommunal', false)
                           ->where(function($q) use ($userCommune) {
                               $q->where('commune', $userCommune)
                                 ->orWhereJsonContains('communes', $userCommune);
                           });
              });
    })
    ->get();
```

---

## 📊 Exemples de Configuration

### Exemple 1: Service Communal (Cocody uniquement)
```php
ServiceType::create([
    'name' => 'Partage Cocody',
    'sharing_type' => 'PDP',
    'is_intercommunal' => false,
    'commune' => 'Cocody',
    'communes' => null,
]);
```

**Résultat**:
- ✅ Visible pour les utilisateurs de Cocody
- ❌ Invisible pour les utilisateurs d'autres communes

### Exemple 2: Service Intercommunal (Toute la ville)
```php
ServiceType::create([
    'name' => 'Partage Abidjan',
    'sharing_type' => 'PDP',
    'is_intercommunal' => true,
    'commune' => null,
    'communes' => ['Cocody', 'Plateau', 'Yopougon', 'Abobo'],
]);
```

**Résultat**:
- ✅ Visible pour tous les utilisateurs d'Abidjan
- ✅ Peut avoir des arrêts dans toutes les communes listées

### Exemple 3: Service Multi-Communes (Cocody + Plateau)
```php
ServiceType::create([
    'name' => 'Partage Cocody-Plateau',
    'sharing_type' => 'PDP',
    'is_intercommunal' => false,
    'commune' => 'Cocody',
    'communes' => ['Cocody', 'Plateau'],
]);
```

**Résultat**:
- ✅ Visible pour Cocody et Plateau
- ❌ Invisible pour les autres communes

---

## 🎯 Validation des Arrêts

### Logique de Validation

Lors de l'ajout d'un arrêt à une route PDP:

```php
// Dans PdpRouteController ou validation
public function validateStopForService($stop, $serviceType)
{
    // Si le service est intercommunal, tous les arrêts sont acceptés
    if ($serviceType->is_intercommunal) {
        return true;
    }
    
    // Si le service est communal, vérifier que l'arrêt est dans les communes autorisées
    $allowedCommunes = $serviceType->communes ?? [$serviceType->commune];
    
    return in_array($stop->commune, $allowedCommunes);
}
```

---

## 📋 Checklist de Migration

### Étapes à Suivre

1. ✅ **Créer la migration** `add_is_intercommunal_to_service_types_table.php`
2. ✅ **Créer la migration** `remove_is_outstation_hub_from_pdp_stops.php`
3. ✅ **Mettre à jour** `ServiceType.php` (fillable + casts)
4. ✅ **Mettre à jour** `PdpStop.php` (retirer is_outstation_hub)
5. ✅ **Mettre à jour** les vues admin (create.blade.php, edit.blade.php)
6. ✅ **Mettre à jour** la logique de filtrage dans les contrôleurs
7. ✅ **Migrer les données** existantes si nécessaire
8. ✅ **Tester** la création de services communaux et intercommunaux

---

## 🔄 Migration des Données Existantes

Si vous avez déjà des données en production:

```php
// Script de migration one-time
use App\ServiceType;
use App\PdpStop;

// Identifier les services qui devraient être intercommunaux
// basé sur les arrêts existants
ServiceType::where('sharing_type', 'PDP')->each(function($service) {
    $routes = $service->routes; // Assuming relation exists
    
    $communes = $routes->flatMap(function($route) {
        return $route->stops->pluck('commune')->unique();
    })->unique();
    
    // Si plus d'une commune, c'est intercommunal
    if ($communes->count() > 1) {
        $service->update([
            'is_intercommunal' => true,
            'communes' => $communes->toArray(),
        ]);
    } else {
        $service->update([
            'is_intercommunal' => false,
            'commune' => $communes->first(),
        ]);
    }
});
```

---

## 📊 Résumé des Changements

### Champs Supprimés
```
❌ pdp_stops.is_outstation_hub
```

### Champs Ajoutés
```
✅ service_types.is_intercommunal (boolean)
```

### Logique Améliorée
```
✅ Filtrage par commune au niveau du service
✅ Validation des arrêts selon le type de service
✅ Interface admin plus claire
```

---

**Document généré le**: 23 Novembre 2025  
**Impact**: ⭐⭐⭐ MAJEUR - Refactoring de la logique métier  
**Status**: 🔧 **CORRECTIONS À APPLIQUER**
