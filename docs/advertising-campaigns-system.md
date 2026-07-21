# Système de Campagnes Publicitaires avec IA

## Vue d'ensemble

Ce document décrit l'implémentation du système de gestion de campagnes publicitaires avec intelligence artificielle pour générer et optimiser le contenu publicitaire, et automatiser la diffusion sur différentes plateformes.

## Architecture

### Base de Données

#### Tables créées :

1. **`ad_campaigns`** : Campagnes publicitaires
   - Informations de base (nom, type, budget, dates)
   - Cible d'audience (JSON)
   - Contenu généré par IA (JSON)
   - Statut (DRAFT, ACTIVE, PAUSED, COMPLETED, CANCELLED)

2. **`ad_contents`** : Contenus publicitaires
   - Types : TEXT, IMAGE, VIDEO, AUDIO, CAROUSEL
   - Titre, headline, description, CTA
   - URLs des médias
   - Mots-clés
   - Indicateur de génération IA

3. **`ad_platforms`** : Plateformes de diffusion
   - Plateformes : GOOGLE_ADS, FACEBOOK_ADS, TIKTOK_ADS, IN_APP, IN_VEHICLE
   - ID de campagne sur la plateforme externe
   - Statut et montant dépensé
   - Configuration spécifique

4. **`campaign_performances`** : Performances des campagnes
   - Métriques quotidiennes (impressions, clics, conversions)
   - CTR, CPC, CPM
   - Métriques supplémentaires par plateforme

5. **`ad_templates`** : Modèles de publicités
   - Templates pré-conçus par type de campagne
   - Structure et exemples

### Modèles Eloquent

- `App\AdCampaign` : Campagne publicitaire
- `App\AdContent` : Contenu publicitaire
- `App\AdPlatform` : Plateforme de diffusion
- `App\CampaignPerformance` : Performances
- `App\AdTemplate` : Modèles de publicités

### Services

#### 1. **AiAdService** (`app/Services/AiAdService.php`)
- Génération de contenu publicitaire avec IA (OpenAI)
- Optimisation du contenu pour différentes plateformes
- Suggestion de mots-clés pertinents
- Adaptation aux spécifications de chaque plateforme

#### 2. **GoogleAdsService** (`app/Services/GoogleAdsService.php`)
- Intégration avec Google Ads API
- Création de campagnes
- Récupération des performances

#### 3. **FacebookAdsService** (`app/Services/FacebookAdsService.php`)
- Intégration avec Facebook Marketing API
- Création de campagnes
- Récupération des performances

#### 4. **TikTokAdsService** (`app/Services/TikTokAdsService.php`)
- Intégration avec TikTok Ads API
- Création de campagnes
- Récupération des performances

#### 5. **AdPlatformService** (`app/Services/AdPlatformService.php`)
- Service unifié pour gérer toutes les plateformes
- Publication automatique sur plusieurs plateformes
- Synchronisation des performances

### Contrôleurs

#### API Utilisateur (`app/Http/Controllers/AdCampaignController.php`)
- `GET /api/ad-campaigns` : Liste des campagnes
- `POST /api/ad-campaigns` : Créer une campagne
- `GET /api/ad-campaigns/{id}` : Détails d'une campagne
- `POST /api/ad-campaigns/generate-content` : Générer du contenu avec IA
- `POST /api/ad-campaigns/{id}/publish` : Publier sur des plateformes
- `GET /api/ad-campaigns/{id}/performance` : Performances
- `GET /api/ad-campaigns/templates` : Templates disponibles

#### Admin (`app/Http/Controllers/Resource/AdCampaignResource.php`)
- CRUD complet pour les campagnes
- Synchronisation des performances
- Gestion depuis l'interface admin

### Vues Admin

- `resources/views/admin/ad-campaign/index.blade.php` : Liste des campagnes
- `resources/views/admin/ad-campaign/create.blade.php` : Créer une campagne
- `resources/views/admin/ad-campaign/edit.blade.php` : Modifier une campagne
- `resources/views/admin/ad-campaign/show.blade.php` : Détails et performances

## Configuration

### Variables d'environnement (.env)

```env
# IA (OpenAI)
OPENAI_API_KEY=your_openai_api_key
OPENAI_API_URL=https://api.openai.com/v1
AI_DEFAULT_MODEL=gpt-4

# Google Ads
GOOGLE_ADS_CLIENT_ID=your_client_id
GOOGLE_ADS_CLIENT_SECRET=your_client_secret
GOOGLE_ADS_REFRESH_TOKEN=your_refresh_token
GOOGLE_ADS_DEVELOPER_TOKEN=your_developer_token
GOOGLE_ADS_CUSTOMER_ID=your_customer_id

# Facebook Ads
FACEBOOK_ADS_ACCESS_TOKEN=your_access_token
FACEBOOK_ADS_ACCOUNT_ID=your_account_id
FACEBOOK_ADS_API_VERSION=v18.0

# TikTok Ads
TIKTOK_ADS_ACCESS_TOKEN=your_access_token
TIKTOK_ADS_ADVERTISER_ID=your_advertiser_id
```

## Fonctionnalités

### 1. Génération de Contenu avec IA

Le système utilise OpenAI (ou autre service IA) pour :
- Générer des headlines accrocheurs
- Créer des descriptions persuasives
- Suggérer des appels à l'action
- Proposer des mots-clés pertinents
- Adapter le contenu au type de campagne et à la cible

### 2. Optimisation par Plateforme

Chaque plateforme a ses propres spécifications :
- **Google Ads** : Headlines max 30 caractères, descriptions max 90 caractères
- **Facebook Ads** : Headlines max 40 caractères, images 1:1
- **TikTok Ads** : Headlines max 80 caractères, vidéos requises (max 60s)

Le service IA optimise automatiquement le contenu pour chaque plateforme.

### 3. Publication Automatique

Les campagnes peuvent être publiées automatiquement sur :
- Google Ads
- Facebook Ads
- TikTok Ads
- In-App (dans l'application)
- In-Vehicle (dans les véhicules)

### 4. Suivi des Performances

Le système suit :
- Impressions
- Clics
- Conversions
- CTR (Click-Through Rate)
- CPC (Cost Per Click)
- CPM (Cost Per Mille)
- Montant dépensé

### 5. Personnalisation

Les campagnes peuvent cibler :
- Âge
- Sexe
- Localisation
- Intérêts
- Comportements

## Utilisation API

### Créer une campagne avec IA

```json
POST /api/ad-campaigns
{
  "name": "Campagne été 2024",
  "campaign_type": "BRAND_AWARENESS",
  "budget": 100000,
  "daily_budget": 10000,
  "start_date": "2024-06-01",
  "end_date": "2024-08-31",
  "target_audience": {
    "age_min": 18,
    "age_max": 45,
    "gender": "ALL",
    "location": "Abidjan"
  },
  "platforms": ["GOOGLE_ADS", "FACEBOOK_ADS"],
  "use_ai": true,
  "business_type": "Transport",
  "publish": true
}
```

### Générer du contenu avec IA

```json
POST /api/ad-campaigns/generate-content
{
  "campaign_type": "LEAD_GENERATION",
  "business_type": "Transport",
  "target_audience": {
    "age_min": 25,
    "age_max": 40
  },
  "budget": 50000
}
```

### Publier une campagne

```json
POST /api/ad-campaigns/{id}/publish
{
  "platforms": ["GOOGLE_ADS", "FACEBOOK_ADS", "TIKTOK_ADS"]
}
```

## Prochaines Étapes

1. **Implémenter les vraies intégrations API** :
   - Compléter Google Ads API
   - Compléter Facebook Marketing API
   - Compléter TikTok Ads API

2. **Améliorer l'IA** :
   - Entraîner des modèles spécifiques
   - Ajouter la génération d'images/vidéos
   - Optimisation continue basée sur les performances

3. **Fonctionnalités supplémentaires** :
   - A/B testing automatique
   - Recommandations d'optimisation
   - Rapports détaillés
   - Export de données

4. **Intégration in-app et in-vehicle** :
   - Affichage des publicités dans l'application
   - Affichage dans les véhicules
   - Ciblage géolocalisé

## Notes Techniques

- Les services d'intégration avec les plateformes sont actuellement en mode simulation
- L'IA utilise OpenAI par défaut, mais peut être adaptée pour d'autres services
- Les performances sont synchronisées manuellement ou via des tâches planifiées
- Le système supporte la personnalisation avec consentement utilisateur

