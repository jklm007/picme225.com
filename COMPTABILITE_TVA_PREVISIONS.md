# 📊 SYSTÈME DE COMPTABILITÉ TVA & PRÉVISIONS - PICME225

## 🎯 OBJECTIF

Fournir un outil complet de gestion de la TVA pour :
- ✅ Suivre la TVA collectée en temps réel
- ✅ Générer des prévisions financières
- ✅ Faciliter les déclarations fiscales UEMOA
- ✅ Anticiper les obligations fiscales

---

## 📈 FONCTIONNALITÉS PRINCIPALES

### 1. **Dashboard Temps Réel**

#### **Statistiques Instantanées**
- 💰 **TVA Collectée** - Montant total du mois
- 🔢 **Nombre de Transactions** - Volume d'activité
- 📊 **Taux Effectif** - Taux réel appliqué
- 💵 **Base Imposable** - Commission totale (assiette TVA)

#### **Alerte Échéance**
```
🚨 Prochaine Échéance : 15 Janvier 2026 (18 jours restants)
📅 Période concernée : Décembre 2025
```

---

### 2. **Historique 12 Mois**

**Graphique Linéaire Interactif** (Chart.js)
- Visualisation de la TVA collectée sur 12 mois glissants
- Identification des tendances saisonnières
- Détection des pics et creux d'activité

**Exemple de Données :**
```
Jan 2025 : 1,250,000 CFA
Fév 2025 : 1,180,000 CFA
Mar 2025 : 1,420,000 CFA
...
Déc 2025 : 1,650,000 CFA
```

---

### 3. **Prévisions Intelligentes (3 Mois)**

#### **Méthodologie de Calcul**

1. **Moyenne Mobile** - Calcul sur les 6 derniers mois
2. **Taux de Croissance** - Comparaison 3 mois récents vs 3 mois anciens
3. **Projection** - Application de la tendance aux mois futurs

#### **Scénarios Multiples**

| Scénario | Calcul | Utilité |
|----------|--------|---------|
| **Pessimiste** | Base × 0.8 (-20%) | Planification prudente |
| **Base** | Tendance actuelle | Prévision réaliste |
| **Optimiste** | Base × 1.2 (+20%) | Potentiel maximum |

#### **Exemple de Prévision**

```
Janvier 2026 :
├─ Pessimiste : 1,320,000 CFA
├─ Base       : 1,650,000 CFA  ← Prévision principale
└─ Optimiste  : 1,980,000 CFA

Tendance : +8.5% (Croissance)
```

---

### 4. **Analyse Trimestrielle**

**Graphique Doughnut** - Répartition par trimestre

```
Année 2025 :
├─ Q1 (Jan-Mar) : 3,850,000 CFA (22%)
├─ Q2 (Avr-Jun) : 4,120,000 CFA (24%)
├─ Q3 (Jul-Sep) : 4,580,000 CFA (26%)
└─ Q4 (Oct-Déc) : 4,950,000 CFA (28%)

Total Année : 17,500,000 CFA
```

---

### 5. **Détails Mensuels**

#### **Tableau Récapitulatif**

| Indicateur | Valeur |
|-----------|--------|
| Revenu Total | 92,000,000 CFA |
| Commission Totale (Base) | 9,200,000 CFA |
| TVA Collectée (18%) | 1,656,000 CFA |
| TVA Paiements en Ligne | 1,150,000 CFA |
| TVA Paiements Cash | 506,000 CFA |
| Nombre de Transactions | 4,850 |

---

### 6. **Export & Rapports**

#### **Rapport Détaillé (HTML/Print)**
- Liste complète des transactions
- Détails : Date, Client, Chauffeur, Montants, Mode paiement
- Totaux et sous-totaux
- Section signature pour certification

#### **Format d'Export**
```
Transaction #1
Date : 15/12/2025 14:30
Client : Jean Kouassi
Chauffeur : Yao Konan
Montant HT : 18,000 CFA
Commission : 1,800 CFA (10%)
TVA : 324 CFA
Mode : Carte Bancaire
```

#### **Génération PDF** (À venir)
- Rapport officiel pour administration fiscale
- Logo et en-tête personnalisés
- Numérotation automatique

---

## 🔢 CALCUL DE LA TVA

### **Formule Appliquée**

```
TVA = Commission × Taux TVA

Exemple :
Course : 20,000 CFA
Commission (10%) : 2,000 CFA
TVA (18%) : 2,000 × 18% = 360 CFA
```

### **Répartition par Mode de Paiement**

| Mode | TVA Collectée | % du Total |
|------|---------------|------------|
| **Carte Bancaire** | 1,150,000 CFA | 69% |
| **Cash** | 506,000 CFA | 31% |
| **TOTAL** | 1,656,000 CFA | 100% |

---

## 📅 CALENDRIER FISCAL UEMOA

### **Obligations Mensuelles**

**Déclaration :** Avant le **15 du mois suivant**

```
Décembre 2025 → Déclaration avant le 15 Janvier 2026
Janvier 2026  → Déclaration avant le 15 Février 2026
Février 2026  → Déclaration avant le 15 Mars 2026
```

### **Alertes Automatiques**

Le système affiche :
- 🟢 **> 15 jours** : Alerte verte
- 🟡 **8-15 jours** : Alerte jaune
- 🔴 **< 8 jours** : Alerte rouge urgente

---

## 📊 EXEMPLES DE PRÉVISIONS

### **Scénario 1 : Croissance Stable (+5%/mois)**

| Mois | Historique | Prévision | Écart |
|------|-----------|-----------|-------|
| Oct 2025 | 1,500,000 | - | - |
| Nov 2025 | 1,575,000 | - | - |
| Déc 2025 | 1,654,000 | - | - |
| **Jan 2026** | - | **1,737,000** | +5% |
| **Fév 2026** | - | **1,824,000** | +5% |
| **Mar 2026** | - | **1,915,000** | +5% |

### **Scénario 2 : Décroissance (-3%/mois)**

| Mois | Historique | Prévision | Écart |
|------|-----------|-----------|-------|
| Oct 2025 | 1,800,000 | - | - |
| Nov 2025 | 1,746,000 | - | - |
| Déc 2025 | 1,694,000 | - | - |
| **Jan 2026** | - | **1,643,000** | -3% |
| **Fév 2026** | - | **1,594,000** | -3% |
| **Mar 2026** | - | **1,546,000** | -3% |

---

## 🎨 INTERFACE UTILISATEUR

### **Dashboard Principal**

```
┌─────────────────────────────────────────────────────┐
│  📊 Comptabilité TVA & Prévisions                   │
│  [Mois: Décembre] [Année: 2025] [Afficher]         │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ 🚨 Prochaine Échéance : 15/01/2026 (18 jours)      │
└─────────────────────────────────────────────────────┘

┌──────────┬──────────┬──────────┬──────────┐
│ TVA      │ Trans.   │ Taux     │ Base     │
│ 1,656K   │ 4,850    │ 18%      │ 9,200K   │
└──────────┴──────────┴──────────┴──────────┘

┌─────────────────────────────────────────────────────┐
│  Historique 12 Mois (Graphique Linéaire)           │
│  [Chart.js Line Chart]                              │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│  Prévisions 3 Mois (Graphique Barres)              │
│  [Chart.js Bar Chart - Pessimiste/Base/Optimiste]  │
└─────────────────────────────────────────────────────┘
```

---

## 🔧 FICHIERS CRÉÉS

### **Backend**
1. `app/Http/Controllers/Admin/TvaAccountingController.php`
   - Logique de calcul et prévisions
   - Génération de rapports

2. `routes/admin.php`
   - Routes dashboard, export, PDF

### **Frontend**
1. `resources/views/admin/tva_accounting/dashboard.blade.php`
   - Interface principale avec graphiques

2. `resources/views/admin/tva_accounting/export.blade.php`
   - Rapport détaillé imprimable

3. `resources/views/admin/include/nav.blade.php`
   - Menu navigation

---

## 🚀 ACCÈS AU SYSTÈME

**URL Dashboard :** `/admin/tva-accounting`

**Menu Admin :**
```
Admin Panel
├── Trésorerie & Liquidité
├── Finances DAO
├── 🆕 Comptabilité TVA  ← NOUVEAU
└── ...
```

---

## 📱 ACTIONS DISPONIBLES

1. **📊 Visualiser** - Dashboard interactif
2. **📅 Filtrer** - Par mois/année
3. **📄 Exporter** - Rapport détaillé HTML
4. **🖨️ Imprimer** - Version papier
5. **📑 PDF** - Génération officielle (à venir)

---

## 💡 CONSEILS D'UTILISATION

### **Pour la Déclaration Mensuelle**

1. Accéder au dashboard avant le **10 du mois**
2. Sélectionner le **mois précédent**
3. Vérifier le montant de **TVA Collectée**
4. Cliquer sur **"Exporter Rapport Détaillé"**
5. Imprimer ou sauvegarder en PDF
6. Soumettre à l'administration fiscale

### **Pour la Planification Budgétaire**

1. Consulter les **prévisions 3 mois**
2. Noter le **scénario pessimiste** (prudence)
3. Provisionner les fonds nécessaires
4. Ajuster selon la **tendance de croissance**

---

## 📞 SUPPORT

**Questions sur la TVA :**
- Dashboard : `/admin/tva-accounting`
- Logs : `storage/logs/laravel.log`
- Documentation : Ce fichier

---

**Dernière mise à jour :** 28 Décembre 2025  
**Version :** 1.0 (Système de Comptabilité TVA)
