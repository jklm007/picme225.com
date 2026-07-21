# 💰 SYSTÈME DE RÉPARTITION FINANCIÈRE DAO - PICME225

## 📊 NOUVELLE STRUCTURE CORRIGÉE (Décembre 2025)

### 🎯 PRINCIPE FONDAMENTAL
**Tous les pourcentages sont calculés sur la COMMISSION, pas sur le montant total de la course.**
Cela garantit que la trésorerie DAO ne peut jamais être négative.

---

## 🏆 NIVEAUX D'ABONNEMENT

| Niveau | Type Commission | Valeur | Prix Mensuel | Priorité | Assurance |
|--------|----------------|--------|--------------|----------|-----------|
| **GOLD** 🥇 | **Fixe** | **50 CFA/course** | 20,000 CFA | 100 (Max) | ✅ Incluse |
| **PRO** ⭐ | Pourcentage | 5% | 15,000 CFA | 80 | ✅ Incluse |
| **ECO** 🌿 | Pourcentage | 10% | 10,000 CFA | 60 | ✅ Incluse |
| **STANDARD** 📦 | Pourcentage | 15% | 5,000 CFA | 40 | ✅ Incluse |
| **NONE** ❌ | Pourcentage | 25% | Gratuit | 20 (Min) | ❌ Non incluse |

---

## 💸 RÉPARTITION DE LA COMMISSION (Pourcentages par Défaut)

### Configuration Actuelle (Modifiable via Dashboard Admin)

| Bénéficiaire | % de la Commission | Destination |
|--------------|-------------------|-------------|
| **TVA** 🏛️ | 18% | Trésor Public (UEMOA) |
| **Assurance DAO** 🛡️ | 15% | Pool Mutuel (Sinistres + Restitution) |
| **Syndicat** 🤝 | 10% | Fonds Syndical Chauffeurs |
| **Coopérative** 🏢 | 10% | Fonds Coopératif |
| **Trésorerie DAO** 💎 | **47%** (Reste) | Développement Plateforme |
| **TOTAL** | **100%** | - |

---

## 🧮 EXEMPLES DE CALCUL

### Exemple 1 : Chauffeur GOLD (Commission Fixe)
```
Course : 20,000 CFA
Commission : 50 CFA (fixe, peu importe le montant)
Chauffeur reçoit : 19,950 CFA

Répartition des 50 CFA :
├─ TVA (18%)           = 9 CFA
├─ Assurance (15%)     = 7.5 CFA
├─ Syndicat (10%)      = 5 CFA
├─ Coopérative (10%)   = 5 CFA
└─ Trésorerie DAO (47%) = 23.5 CFA
─────────────────────────────
Total = 50 CFA ✅
```

### Exemple 2 : Chauffeur ECO (Commission 10%)
```
Course : 20,000 CFA
Commission : 2,000 CFA (10%)
Chauffeur reçoit : 18,000 CFA

Répartition des 2,000 CFA :
├─ TVA (18%)           = 360 CFA
├─ Assurance (15%)     = 300 CFA
├─ Syndicat (10%)      = 200 CFA
├─ Coopérative (10%)   = 200 CFA
└─ Trésorerie DAO (47%) = 940 CFA
─────────────────────────────
Total = 2,000 CFA ✅
```

### Exemple 3 : Chauffeur NONE (Commission 25%)
```
Course : 10,000 CFA
Commission : 2,500 CFA (25%)
Chauffeur reçoit : 7,500 CFA

Répartition des 2,500 CFA :
├─ TVA (18%)           = 450 CFA
├─ Assurance (15%)     = 375 CFA
├─ Syndicat (10%)      = 250 CFA
├─ Coopérative (10%)   = 250 CFA
└─ Trésorerie DAO (47%) = 1,175 CFA
─────────────────────────────
Total = 2,500 CFA ✅
```

---

## 📈 COMPARAISON ÉCONOMIQUE POUR LES CHAUFFEURS

### Scénario : 100 courses de 10,000 CFA/mois

| Niveau | Commission/Course | Total Commission | Gain Net Chauffeur | Coût Abonnement | **Gain Final** |
|--------|------------------|------------------|-------------------|----------------|----------------|
| GOLD   | 50 CFA           | 5,000 CFA        | 995,000 CFA       | -20,000 CFA    | **975,000 CFA** 🥇 |
| PRO    | 500 CFA (5%)     | 50,000 CFA       | 950,000 CFA       | -15,000 CFA    | **935,000 CFA** |
| ECO    | 1,000 CFA (10%)  | 100,000 CFA      | 900,000 CFA       | -10,000 CFA    | **890,000 CFA** |
| STANDARD| 1,500 CFA (15%) | 150,000 CFA      | 850,000 CFA       | -5,000 CFA     | **845,000 CFA** |
| NONE   | 2,500 CFA (25%)  | 250,000 CFA      | 750,000 CFA       | 0 CFA          | **750,000 CFA** |

**💡 Conclusion :** Le plan GOLD est le plus rentable pour les chauffeurs actifs (>40 courses/mois).

---

## 🎛️ DASHBOARD ADMIN - FONCTIONNALITÉS

### 1. Vue d'Ensemble Temps Réel
- ✅ Nombre total de courses
- ✅ Revenu total généré
- ✅ Commission totale prélevée
- ✅ Trésorerie DAO accumulée

### 2. Filtres Temporels
- Aujourd'hui
- Cette semaine
- Ce mois
- Cette année
- Tout l'historique

### 3. Graphique de Répartition
- 🍩 Graphique Doughnut (Chart.js)
- Visualisation en temps réel des distributions

### 4. Analyse par Niveau d'Abonnement
- Nombre de courses par niveau
- Commission totale par niveau
- Commission moyenne par niveau

### 5. Configuration Dynamique
- ⚙️ Modification des pourcentages de répartition
- ✅ Validation automatique (total ≤ 100%)
- 🔄 Mise à jour en temps réel

### 6. Gestion des Plans
- Visualisation des plans actifs
- Nombre de chauffeurs par plan
- Type de commission (fixe/pourcentage)

---

## 🔧 FICHIERS MODIFIÉS

### Backend
1. `app/Services/DaoDistributionService.php` - Logique de calcul corrigée
2. `app/Http/Controllers/Admin/DaoFinanceController.php` - Dashboard controller
3. `database/migrations/2025_12_28_041746_add_gold_subscription_level.php` - Niveau GOLD
4. `routes/admin.php` - Routes dashboard

### Frontend Admin
1. `resources/views/admin/dao_finance/dashboard.blade.php` - Interface complète
2. `resources/views/admin/include/nav.blade.php` - Menu navigation

---

## 🚀 ACCÈS AU DASHBOARD

**URL :** `https://votre-domaine.com/admin/dao-finance`

**Fonctionnalités :**
- 📊 Visualisation en temps réel
- ⚙️ Configuration des pourcentages
- 📈 Analyse par période
- 💰 Suivi de trésorerie

---

## ⚠️ NOTES IMPORTANTES

1. **Tous les calculs sont basés sur la commission** pour éviter les montants négatifs
2. **Le niveau GOLD utilise une commission fixe** de 50 CFA par course
3. **Les pourcentages sont modifiables** via le dashboard admin
4. **La somme des pourcentages ne peut pas dépasser 100%**
5. **Le reste va automatiquement à la Trésorerie DAO**

---

## 📞 SUPPORT TECHNIQUE

Pour toute question sur la répartition financière :
- Dashboard Admin : `/admin/dao-finance`
- Logs : `storage/logs/laravel.log`
- Documentation : Ce fichier

---

**Dernière mise à jour :** 28 Décembre 2025
**Version :** 2.0 (Système Hybride Dynamique)
