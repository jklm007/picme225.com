# 🔍 Analyse des Migrations ServiceType et Vues Admin

**Date**: 23 Novembre 2025  
**Projet**: Picme225.com  
**Objectif**: Vérifier les migrations ServiceType et l'utilisation dans les vues admin

---

## 📋 Migrations ServiceType Trouvées (17 fichiers)

### 1. Migration Principale
```
✅ 2017_01_08_182738_create_service_types_table.php
```

**Champs créés**:
- `id` (bigIncrements)
- `name` - Nom du service
- `provider_name` - Nom du fournisseur
- `image` - Image du service
- `capacity` - Capacité de sièges
- `fixed` - Prix de base
- `price` - Prix par unité de distance
- `minute` - Prix par minute
- `hour` - Prix par heure (nullable)
- `distance` - Distance de base
- `calculator` - Logique de tarification (ENUM)
- `description` - Description
- `status` - Statut (actif/inactif)

### 2. Migrations d'Ajout de Fonctionnalités

#### 📅 2018_04_23 - Rental Amount
```php
add_rental_amount_to_service_types_table.php
```
- Ajoute: `rental_amount`

#### 🚑 2018_05_02 - Ambulance
```php
add_ambulance_to_service_types_table.php
```
- Ajoute: `ambulance` (boolean)

#### 🚗 2018_08_03 - Outstation
```php
add_outstation_price_to_service_types_table.php
```
- Ajoute: `outstation_price`

#### 📅 2025_01_19 - Day Package
```php
add_day_to_service_types_table.php
```
- Ajoute: `day` (prix package jour)

#### 📊 2025_01_19 - Calculator ENUM Update
```php
update_service_types_calculator_enum.php
```
- Met à jour ENUM calculator

#### 🤝 2025_07_11 - **SHARING TYPE** ⭐
```php
add_sharing_type_to_service_types_table.php
```

**Champ ajouté**:
```php
$table->enum('sharing_type', ['NONE', 'DYNAMIC_POOL', 'PDP'])
      ->default('NONE')
      ->after('status');
```

**Types de partage**:
- `NONE` - Service standard (pas de partage)
- `DYNAMIC_POOL` - Covoiturage dynamique instantané
- `PDP` - Routes planifiées (Point de Passage)

#### 📍 2025_11_18 - Partage Fields
```php
add_partage_fields_to_service_types_table.php
```
⚠️ **FICHIER VIDE** - Migration non implémentée!

#### 💰 2025_11_19 - Price Per KM
```php
add_price_per_km_to_service_types_table.php
```
- Ajoute: `price_per_km` pour le partage

---

## 🔍 Analyse des Vues Admin

### Vues Trouvées pour ServiceType

#### 1. **create.blade.php** - Création de Service
**Localisation**: `resources/views/admin/service/create.blade.php`

**Champs présents** ✅:
- ✅ Ambulance (checkbox)
- ✅ Name
- ✅ Provider Name
- ✅ Image
- ✅ Calculator (logique de tarification)
- ✅ Hour Price
- ✅ Base Price (fixed)
- ✅ Base Distance
- ✅ Rental Amount
- ✅ Day Package
- ✅ Unit Time Pricing (minute)
- ✅ Unit Distance Price (price)
- ✅ Seat Capacity
- ✅ Description
- ✅ Outstation Price
- ✅ Rental Prices (km/hours)

**Champs MANQUANTS** ❌:
- ❌ **sharing_type** - Type de partage (NONE/DYNAMIC_POOL/PDP)
- ❌ **commune** - Restriction par commune
- ❌ **price_per_km** - Prix par km pour partage
- ❌ **max_detour** - Détour maximum autorisé
- ❌ **max_waiting_time** - Temps d'attente maximum

#### 2. **edit.blade.php** - Édition de Service
**Localisation**: `resources/views/admin/service/edit.blade.php`

**Même problème**: Champs de partage manquants

#### 3. **index.blade.php** - Liste des Services
**Localisation**: `resources/views/admin/service/index.blade.php`

**Colonnes affichées**: À vérifier

---

## ❌ Problèmes Identifiés

### 1. **Migration Vide**
```
❌ 2025_11_18_000001_add_partage_fields_to_service_types_table.php
```
**Problème**: Fichier complètement vide  
**Impact**: Les champs de partage ne sont pas créés en base de données

### 2. **Formulaires Admin Incomplets**
```
❌ create.blade.php - Manque champs de partage
❌ edit.blade.php - Manque champs de partage
```
**Problème**: Impossible de configurer le type de partage depuis l'admin  
**Impact**: Fonctionnalité de partage non accessible

### 3. **Pas de Restriction par Commune**
```
❌ Aucun champ "commune" dans les migrations
❌ Aucun champ "commune" dans les formulaires
```
**Problème**: Impossible de restreindre un service à une commune spécifique  
**Impact**: Services disponibles partout sans restriction géographique

---

## 🔧 Corrections Nécessaires

### 1. Créer la Migration Manquante

**Fichier**: `2025_11_18_000001_add_partage_fields_to_service_types_table.php`

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
            // Champs pour le service partagé
            $table->decimal('max_detour', 8, 2)->nullable()->after('sharing_type')
                  ->comment('Détour maximum autorisé en km pour porte-à-porte');
            
            $table->integer('max_waiting_time')->nullable()->after('max_detour')
                  ->comment('Temps d\'attente maximum en minutes');
            
            $table->decimal('detour_price_per_km', 8, 2)->nullable()->after('max_waiting_time')
                  ->comment('Prix par km pour les détours porte-à-porte');
            
            // Restriction géographique
            $table->string('commune')->nullable()->after('detour_price_per_km')
                  ->comment('Commune où le service est disponible (null = toutes)');
            
            $table->json('communes')->nullable()->after('commune')
                  ->comment('Liste des communes autorisées (pour multi-communes)');
        });
    }

    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn([
                'max_detour',
                'max_waiting_time',
                'detour_price_per_km',
                'commune',
                'communes'
            ]);
        });
    }
};
```

### 2. Mettre à Jour create.blade.php

**Ajouter après le champ "Description" (ligne 137)**:

```html
<!-- Section Partage -->
<hr>
<h6 class="mb-3"><strong>Configuration du Partage</strong></h6>

<!-- Type de partage -->
<div class="form-group row">
    <label for="sharing_type" class="col-xs-12 col-form-label">Type de Partage</label>
    <div class="col-xs-10">
        <select class="form-control" id="sharing_type" name="sharing_type" required>
            <option value="NONE" {{ old('sharing_type') == 'NONE' ? 'selected' : '' }}>
                Aucun (Service Standard)
            </option>
            <option value="DYNAMIC_POOL" {{ old('sharing_type') == 'DYNAMIC_POOL' ? 'selected' : '' }}>
                Covoiturage Dynamique
            </option>
            <option value="PDP" {{ old('sharing_type') == 'PDP' ? 'selected' : '' }}>
                Routes Planifiées (PDP)
            </option>
        </select>
        <small class="form-text text-muted">
            NONE: Service classique | DYNAMIC_POOL: Partage instantané | PDP: Routes fixes
        </small>
    </div>
</div>

<!-- Champs conditionnels pour le partage -->
<div id="sharing_fields" style="display: none;">
    <!-- Détour maximum -->
    <div class="form-group row">
        <label for="max_detour" class="col-xs-12 col-form-label">Détour Maximum (km)</label>
        <div class="col-xs-10">
            <input class="form-control" type="number" step="0.1" value="{{ old('max_detour', 2.0) }}" 
                   name="max_detour" id="max_detour" placeholder="2.0">
            <small class="form-text text-muted">
                Détour maximum autorisé pour le porte-à-porte
            </small>
        </div>
    </div>

    <!-- Temps d'attente maximum -->
    <div class="form-group row">
        <label for="max_waiting_time" class="col-xs-12 col-form-label">Temps d'Attente Max (minutes)</label>
        <div class="col-xs-10">
            <input class="form-control" type="number" value="{{ old('max_waiting_time', 10) }}" 
                   name="max_waiting_time" id="max_waiting_time" placeholder="10">
            <small class="form-text text-muted">
                Temps d'attente maximum pour récupérer les passagers
            </small>
        </div>
    </div>

    <!-- Prix détour par km -->
    <div class="form-group row">
        <label for="detour_price_per_km" class="col-xs-12 col-form-label">
            Prix Détour par km ({{ currency() }})
        </label>
        <div class="col-xs-10">
            <input class="form-control" type="number" step="0.01" value="{{ old('detour_price_per_km') }}" 
                   name="detour_price_per_km" id="detour_price_per_km" placeholder="150">
            <small class="form-text text-muted">
                Prix facturé par km de détour pour le porte-à-porte
            </small>
        </div>
    </div>
</div>

<!-- Restriction géographique -->
<hr>
<h6 class="mb-3"><strong>Restriction Géographique</strong></h6>

<div class="form-group row">
    <label for="commune" class="col-xs-12 col-form-label">Commune Principale</label>
    <div class="col-xs-10">
        <select class="form-control" id="commune" name="commune">
            <option value="">Toutes les communes</option>
            <option value="Cocody" {{ old('commune') == 'Cocody' ? 'selected' : '' }}>Cocody</option>
            <option value="Plateau" {{ old('commune') == 'Plateau' ? 'selected' : '' }}>Plateau</option>
            <option value="Yopougon" {{ old('commune') == 'Yopougon' ? 'selected' : '' }}>Yopougon</option>
            <option value="Abobo" {{ old('commune') == 'Abobo' ? 'selected' : '' }}>Abobo</option>
            <option value="Adjamé" {{ old('commune') == 'Adjamé' ? 'selected' : '' }}>Adjamé</option>
            <option value="Marcory" {{ old('commune') == 'Marcory' ? 'selected' : '' }}>Marcory</option>
            <option value="Treichville" {{ old('commune') == 'Treichville' ? 'selected' : '' }}>Treichville</option>
            <option value="Koumassi" {{ old('commune') == 'Koumassi' ? 'selected' : '' }}>Koumassi</option>
            <option value="Port-Bouët" {{ old('commune') == 'Port-Bouët' ? 'selected' : '' }}>Port-Bouët</option>
            <option value="Attécoubé" {{ old('commune') == 'Attécoubé' ? 'selected' : '' }}>Attécoubé</option>
        </select>
        <small class="form-text text-muted">
            Laissez vide pour rendre le service disponible dans toutes les communes
        </small>
    </div>
</div>

<div class="form-group row">
    <label for="communes_multi" class="col-xs-12 col-form-label">Communes Additionnelles</label>
    <div class="col-xs-10">
        <select class="form-control" id="communes_multi" name="communes[]" multiple size="5">
            <option value="Cocody">Cocody</option>
            <option value="Plateau">Plateau</option>
            <option value="Yopougon">Yopougon</option>
            <option value="Abobo">Abobo</option>
            <option value="Adjamé">Adjamé</option>
            <option value="Marcory">Marcory</option>
            <option value="Treichville">Treichville</option>
            <option value="Koumassi">Koumassi</option>
            <option value="Port-Bouët">Port-Bouët</option>
            <option value="Attécoubé">Attécoubé</option>
        </select>
        <small class="form-text text-muted">
            Maintenez Ctrl (Cmd sur Mac) pour sélectionner plusieurs communes
        </small>
    </div>
</div>
```

### 3. Ajouter le JavaScript

**Dans la section @section('scripts'), ajouter**:

```javascript
<script>
$(document).ready(function(){
    // Gestion de l'affichage des champs de partage
    function toggleSharingFields() {
        var sharingType = $("#sharing_type").val();
        if(sharingType === 'DYNAMIC_POOL' || sharingType === 'PDP') {
            $("#sharing_fields").show();
        } else {
            $("#sharing_fields").hide();
        }
    }
    
    // Initialiser au chargement
    toggleSharingFields();
    
    // Écouter les changements
    $("#sharing_type").change(function(){
        toggleSharingFields();
    });
    
    // Existing hour price logic
    $("#hour_price").hide();
    $("#calculator").change(function(){
        if($("#calculator").val() == 'DISTANCEHOUR'){
            $("#hour_price").show();
        } else {
            $("#hour_price").hide();
        }
    });
});
</script>
```

### 4. Mettre à Jour le Contrôleur

**Fichier**: `app/Http/Controllers/Resource/ServiceResource.php`

**Méthode `store()`** - Ajouter la validation:

```php
$validatedData = $request->validate([
    // ... champs existants ...
    'sharing_type' => 'required|in:NONE,DYNAMIC_POOL,PDP',
    'max_detour' => 'nullable|numeric|min:0',
    'max_waiting_time' => 'nullable|integer|min:0',
    'detour_price_per_km' => 'nullable|numeric|min:0',
    'commune' => 'nullable|string|max:100',
    'communes' => 'nullable|array',
]);

// Convertir le tableau communes en JSON
if ($request->has('communes')) {
    $validatedData['communes'] = json_encode($request->communes);
}
```

---

## 📊 Résumé des Modifications

### Migrations à Créer/Corriger
1. ✅ Remplir `add_partage_fields_to_service_types_table.php`

### Vues à Mettre à Jour
1. ✅ `create.blade.php` - Ajouter champs partage + commune
2. ✅ `edit.blade.php` - Ajouter champs partage + commune
3. ✅ `index.blade.php` - Afficher sharing_type et commune

### Contrôleurs à Modifier
1. ✅ `ServiceResource@store` - Validation + sauvegarde
2. ✅ `ServiceResource@update` - Validation + sauvegarde

### Modèle à Mettre à Jour
1. ✅ `ServiceType.php` - Ajouter dans `$fillable`:
   ```php
   'sharing_type',
   'max_detour',
   'max_waiting_time',
   'detour_price_per_km',
   'commune',
   'communes'
   ```

---

## 🎯 Impact Fonctionnel

### Avec ces Modifications

#### 1. **Gestion du Type de Partage**
- ✅ Admin peut choisir NONE/DYNAMIC_POOL/PDP
- ✅ Configuration des paramètres de partage
- ✅ Prix détour configurables

#### 2. **Restriction par Commune**
- ✅ Service limité à une commune spécifique
- ✅ Ou plusieurs communes
- ✅ Ou toutes les communes (par défaut)

#### 3. **Filtrage API**
```php
// Dans UserApiController@services()
$services = ServiceType::where('status', 1)
    ->where(function($query) use ($userCommune) {
        $query->whereNull('commune')
              ->orWhere('commune', $userCommune)
              ->orWhereJsonContains('communes', $userCommune);
    })
    ->get();
```

---

## 📝 Prochaines Étapes

1. ✅ Créer la migration manquante
2. ✅ Mettre à jour les vues admin
3. ✅ Modifier le contrôleur
4. ✅ Mettre à jour le modèle
5. ✅ Tester la création de services
6. ✅ Tester le filtrage par commune
7. ✅ Documenter les nouveaux champs

---

**Rapport généré le**: 23 Novembre 2025  
**Status**: ⚠️ **CORRECTIONS NÉCESSAIRES**
