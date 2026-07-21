# Plan d'Intégration DAO + Token ECO + Mobile Money

## Vue d'ensemble

Ce document détaille le plan d'intégration de trois fonctionnalités majeures :
1. **DAO (Decentralized Autonomous Organization)** - Gouvernance décentralisée
2. **Token ECO** - Token ERC-20 pour récompenses et paiements
3. **Mobile Money** - Intégration des paiements mobiles (Orange Money, MTN, Moov)

---

## 1. DAO (Decentralized Autonomous Organization)

### 1.1 Architecture Technique

#### Stack Technologique
- **Blockchain** : Ethereum / Polygon (pour réduire les coûts de gas)
- **Framework** : Hardhat (développement et déploiement)
- **Langage** : Solidity ^0.8.0
- **Bibliothèque Laravel** : `web3.php` ou `ethereum-php`

#### Structure du Contrat Intelligent

```solidity
// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

contract PDPDao {
    // Types de propositions
    enum ProposalType {
        PRICE_CHANGE,      // Changement de prix
        ROUTE_ADDITION,    // Ajout d'itinéraire
        ROUTE_MODIFICATION,// Modification d'itinéraire
        PARAMETER_CHANGE   // Changement de paramètres
    }
    
    // Statut des propositions
    enum ProposalStatus {
        PENDING,    // En attente de vote
        ACTIVE,     // Vote en cours
        PASSED,     // Approuvée
        REJECTED,   // Rejetée
        EXECUTED    // Exécutée
    }
    
    struct Proposal {
        uint256 id;
        address proposer;
        ProposalType proposalType;
        string title;
        string description;
        uint256 startTime;
        uint256 endTime;
        uint256 votesFor;
        uint256 votesAgainst;
        uint256 votesAbstain;
        ProposalStatus status;
        mapping(address => bool) hasVoted;
        bytes executionData; // Données pour l'exécution
    }
    
    // Paramètres de gouvernance
    uint256 public quorum; // Quorum minimum (ex: 10% des tokens)
    uint256 public votingPeriod; // Durée du vote (ex: 7 jours)
    uint256 public proposalThreshold; // Minimum de tokens pour proposer
    
    // Référence au contrat token ECO
    IERC20 public ecoToken;
    
    // Mapping des propositions
    mapping(uint256 => Proposal) public proposals;
    uint256 public proposalCount;
    
    // Événements
    event ProposalCreated(uint256 indexed proposalId, address indexed proposer);
    event VoteCast(uint256 indexed proposalId, address indexed voter, bool support);
    event ProposalExecuted(uint256 indexed proposalId);
}
```

### 1.2 Fonctionnalités du Contrat

#### Fonctions Principales

1. **Créer une proposition**
   ```solidity
   function createProposal(
       ProposalType _type,
       string memory _title,
       string memory _description,
       bytes memory _executionData
   ) external returns (uint256)
   ```

2. **Voter sur une proposition**
   ```solidity
   function vote(
       uint256 _proposalId,
       bool _support // true = pour, false = contre
   ) external
   ```

3. **Exécuter une proposition approuvée**
   ```solidity
   function executeProposal(uint256 _proposalId) external
   ```

4. **Consulter les propositions**
   ```solidity
   function getProposal(uint256 _proposalId) external view returns (...)
   ```

### 1.3 API Laravel

#### Structure des Contrôleurs

**`app/Http/Controllers/Dao/ProposalController.php`**
```php
- index() : Liste des propositions
- show($id) : Détails d'une proposition
- store(Request $request) : Créer une proposition
- vote(Request $request, $id) : Voter sur une proposition
- execute($id) : Exécuter une proposition (admin uniquement)
```

**`app/Http/Controllers/Dao/VoteController.php`**
```php
- myVotes() : Votes de l'utilisateur
- proposalVotes($proposalId) : Votes d'une proposition
```

#### Routes API

```php
// routes/api.php
Route::group(['prefix' => 'dao', 'middleware' => ['auth:api']], function () {
    Route::get('/proposals', [ProposalController::class, 'index']);
    Route::get('/proposals/{id}', [ProposalController::class, 'show']);
    Route::post('/proposals', [ProposalController::class, 'store']);
    Route::post('/proposals/{id}/vote', [ProposalController::class, 'vote']);
    Route::get('/proposals/{id}/votes', [VoteController::class, 'proposalVotes']);
    Route::get('/my-votes', [VoteController::class, 'myVotes']);
});
```

#### Service Web3

**`app/Services/Web3Service.php`**
```php
class Web3Service {
    private $web3;
    private $contractAddress;
    private $contractAbi;
    
    public function createProposal($type, $title, $description, $executionData);
    public function vote($proposalId, $support, $userAddress);
    public function getProposal($proposalId);
    public function getProposalVotes($proposalId);
    public function executeProposal($proposalId);
}
```

### 1.4 Base de Données

#### Migration : `create_dao_proposals_table.php`
```php
Schema::create('dao_proposals', function (Blueprint $table) {
    $table->id();
    $table->string('blockchain_proposal_id'); // ID sur la blockchain
    $table->unsignedBigInteger('user_id');
    $table->enum('type', ['PRICE_CHANGE', 'ROUTE_ADDITION', 'ROUTE_MODIFICATION', 'PARAMETER_CHANGE']);
    $table->string('title');
    $table->text('description');
    $table->json('execution_data')->nullable();
    $table->enum('status', ['PENDING', 'ACTIVE', 'PASSED', 'REJECTED', 'EXECUTED']);
    $table->timestamp('start_time');
    $table->timestamp('end_time');
    $table->bigInteger('votes_for')->default(0);
    $table->bigInteger('votes_against')->default(0);
    $table->bigInteger('votes_abstain')->default(0);
    $table->boolean('executed')->default(false);
    $table->timestamps();
});
```

#### Migration : `create_dao_votes_table.php`
```php
Schema::create('dao_votes', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('proposal_id');
    $table->unsignedBigInteger('user_id');
    $table->string('user_wallet_address');
    $table->enum('vote', ['FOR', 'AGAINST', 'ABSTAIN']);
    $table->bigInteger('token_amount'); // Nombre de tokens utilisés pour voter
    $table->string('transaction_hash')->nullable();
    $table->timestamps();
    
    $table->unique(['proposal_id', 'user_id']);
});
```

### 1.5 Application Android

#### Interfaces Utilisateur

1. **Liste des propositions**
   - RecyclerView avec filtres (actives, passées, rejetées)
   - Indicateur de statut
   - Compteur de votes

2. **Détails d'une proposition**
   - Titre, description, type
   - Statut et dates
   - Résultats des votes
   - Bouton de vote (si active)

3. **Créer une proposition**
   - Formulaire avec type, titre, description
   - Validation des tokens minimum
   - Confirmation de transaction

4. **Voter**
   - Sélection du vote (pour/contre/abstention)
   - Affichage du poids du vote (basé sur les tokens)
   - Confirmation de transaction

---

## 2. Token ECO (ERC-20)

### 2.1 Contrat Intelligent

```solidity
// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

import "@openzeppelin/contracts/token/ERC20/ERC20.sol";
import "@openzeppelin/contracts/access/Ownable.sol";

contract EcoToken is ERC20, Ownable {
    uint256 public constant MAX_SUPPLY = 1000000000 * 10**18; // 1 milliard
    
    // Adresse autorisée pour le minting (backend Laravel)
    address public minter;
    
    constructor() ERC20("ECO Token", "ECO") {
        minter = msg.sender;
    }
    
    // Minting réservé au backend
    function mint(address to, uint256 amount) external onlyMinter {
        require(totalSupply() + amount <= MAX_SUPPLY, "Max supply exceeded");
        _mint(to, amount);
    }
    
    // Burning pour ajuster l'offre
    function burn(uint256 amount) external {
        _burn(msg.sender, amount);
    }
    
    modifier onlyMinter() {
        require(msg.sender == minter, "Not authorized");
        _;
    }
    
    function setMinter(address _minter) external onlyOwner {
        minter = _minter;
    }
}
```

### 2.2 Distribution des Tokens

#### Mécanismes de Distribution

1. **Airdrop Initial**
   - Distribution aux utilisateurs existants
   - Montant basé sur l'ancienneté et l'activité

2. **Récompenses de Fidélité**
   - Par course effectuée : X tokens
   - Par contribution communautaire : Y tokens
   - Par vote dans le DAO : Z tokens

3. **Récompenses Providers**
   - Par course effectuée : X tokens
   - Par note élevée : Bonus tokens
   - Par ponctualité : Bonus tokens

### 2.3 API Laravel

#### Contrôleur : `app/Http/Controllers/EcoToken/TokenController.php`

```php
class TokenController extends Controller {
    // Obtenir le solde de tokens
    public function balance(Request $request);
    
    // Historique des transactions
    public function transactions(Request $request);
    
    // Distribuer des récompenses
    public function distributeReward(Request $request);
    
    // Payer avec des tokens
    public function payWithTokens(Request $request);
    
    // Transférer des tokens
    public function transfer(Request $request);
}
```

#### Service : `app/Services/EcoTokenService.php`

```php
class EcoTokenService {
    private $web3;
    private $contractAddress;
    private $contractAbi;
    
    // Obtenir le solde d'un wallet
    public function getBalance($walletAddress);
    
    // Mint des tokens (récompenses)
    public function mint($to, $amount);
    
    // Transfert de tokens
    public function transfer($from, $to, $amount);
    
    // Burn des tokens
    public function burn($from, $amount);
    
    // Vérifier une transaction
    public function verifyTransaction($txHash);
}
```

#### Routes API

```php
Route::group(['prefix' => 'eco-token', 'middleware' => ['auth:api']], function () {
    Route::get('/balance', [TokenController::class, 'balance']);
    Route::get('/transactions', [TokenController::class, 'transactions']);
    Route::post('/transfer', [TokenController::class, 'transfer']);
    Route::post('/pay', [TokenController::class, 'payWithTokens']);
});
```

### 2.4 Base de Données

#### Migration : `create_eco_token_transactions_table.php`

```php
Schema::create('eco_token_transactions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->string('wallet_address');
    $table->enum('type', ['MINT', 'TRANSFER', 'BURN', 'REWARD', 'PAYMENT']);
    $table->decimal('amount', 20, 8); // Support des décimales
    $table->string('transaction_hash')->nullable();
    $table->enum('status', ['PENDING', 'CONFIRMED', 'FAILED']);
    $table->string('reference_type')->nullable(); // ride_booking, dao_vote, etc.
    $table->unsignedBigInteger('reference_id')->nullable();
    $table->text('metadata')->nullable(); // JSON
    $table->timestamps();
});
```

#### Migration : `add_wallet_address_to_users_table.php`

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('wallet_address')->nullable()->unique();
    $table->decimal('eco_token_balance', 20, 8)->default(0);
});
```

### 2.5 Intégration avec les Paiements

#### Modifier `RideBooking` pour accepter les tokens

```php
// Dans le contrôleur de réservation
if ($request->payment_mode === 'ECO_TOKEN') {
    $tokenAmount = $this->calculateTokenAmount($totalPrice);
    $userBalance = $user->eco_token_balance;
    
    if ($userBalance < $tokenAmount) {
        return response()->json(['error' => 'Solde de tokens insuffisant'], 400);
    }
    
    // Débiter les tokens
    $this->ecoTokenService->transfer(
        $user->wallet_address,
        $systemWalletAddress,
        $tokenAmount
    );
}
```

---

## 3. Mobile Money

### 3.1 Choix des Fournisseurs

#### Fournisseurs Recommandés

1. **Orange Money Côte d'Ivoire**
   - API officielle Orange Money
   - Documentation : https://developer.orange.com/

2. **MTN Mobile Money**
   - API MTN MoMo
   - Documentation : https://momodeveloper.mtn.com/

3. **Moov Money**
   - API Moov Money
   - Documentation spécifique à contacter

### 3.2 Architecture d'Intégration

#### Service Unifié : `app/Services/MobileMoneyService.php`

```php
interface MobileMoneyProviderInterface {
    public function initiatePayment($amount, $phoneNumber, $reference);
    public function verifyTransaction($transactionId);
    public function checkBalance();
}

class OrangeMoneyService implements MobileMoneyProviderInterface { ... }
class MTNMobileMoneyService implements MobileMoneyProviderInterface { ... }
class MoovMoneyService implements MobileMoneyProviderInterface { ... }

class MobileMoneyService {
    private $provider;
    
    public function __construct($providerName) {
        switch($providerName) {
            case 'orange':
                $this->provider = new OrangeMoneyService();
                break;
            case 'mtn':
                $this->provider = new MTNMobileMoneyService();
                break;
            case 'moov':
                $this->provider = new MoovMoneyService();
                break;
        }
    }
    
    public function initiatePayment($amount, $phoneNumber, $reference);
    public function verifyTransaction($transactionId);
}
```

### 3.3 Configuration Sécurisée

#### Fichier `.env`

```env
# Orange Money
ORANGE_MONEY_API_URL=https://api.orange.com
ORANGE_MONEY_CLIENT_ID=your_client_id
ORANGE_MONEY_CLIENT_SECRET=your_client_secret
ORANGE_MONEY_MERCHANT_KEY=your_merchant_key

# MTN Mobile Money
MTN_MOMO_API_URL=https://sandbox.momodeveloper.mtn.com
MTN_MOMO_SUBSCRIPTION_KEY=your_subscription_key
MTN_MOMO_API_USER=your_api_user
MTN_MOMO_API_KEY=your_api_key

# Moov Money
MOOV_MONEY_API_URL=https://api.moovmoney.com
MOOV_MONEY_API_KEY=your_api_key
MOOV_MONEY_MERCHANT_ID=your_merchant_id
```

#### Service de Configuration : `app/Services/MobileMoneyConfigService.php`

```php
class MobileMoneyConfigService {
    public static function getProviderConfig($provider) {
        return [
            'api_url' => config("mobile_money.{$provider}.api_url"),
            'api_key' => config("mobile_money.{$provider}.api_key"),
            // ... autres configs
        ];
    }
    
    public static function encryptApiKey($key) {
        return encrypt($key); // Utiliser le chiffrement Laravel
    }
}
```

### 3.4 API Laravel

#### Contrôleur : `app/Http/Controllers/MobileMoney/PaymentController.php`

```php
class PaymentController extends Controller {
    // Initier un paiement
    public function initiatePayment(Request $request) {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:100',
            'phone_number' => 'required|string',
            'provider' => 'required|in:orange,mtn,moov',
            'reference' => 'required|string', // ID de la réservation
        ]);
        
        $mobileMoneyService = new MobileMoneyService($request->provider);
        $transaction = $mobileMoneyService->initiatePayment(
            $request->amount,
            $request->phone_number,
            $request->reference
        );
        
        // Enregistrer la transaction
        MobileMoneyTransaction::create([
            'user_id' => Auth::id(),
            'provider' => $request->provider,
            'amount' => $request->amount,
            'phone_number' => $request->phone_number,
            'reference' => $request->reference,
            'transaction_id' => $transaction['id'],
            'status' => 'PENDING',
        ]);
        
        return response()->json($transaction);
    }
    
    // Vérifier le statut d'une transaction
    public function verifyTransaction($transactionId) {
        $transaction = MobileMoneyTransaction::where('transaction_id', $transactionId)->first();
        $mobileMoneyService = new MobileMoneyService($transaction->provider);
        
        $status = $mobileMoneyService->verifyTransaction($transactionId);
        
        $transaction->update(['status' => $status]);
        
        return response()->json(['status' => $status]);
    }
    
    // Webhook pour les notifications
    public function webhook(Request $request) {
        // Vérifier la signature
        if (!$this->verifyWebhookSignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        
        // Traiter la notification
        $this->processWebhookNotification($request->all());
        
        return response()->json(['status' => 'ok']);
    }
}
```

#### Routes API

```php
Route::group(['prefix' => 'mobile-money', 'middleware' => ['auth:api']], function () {
    Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment']);
    Route::get('/payment/verify/{transactionId}', [PaymentController::class, 'verifyTransaction']);
    Route::get('/transactions', [PaymentController::class, 'transactions']);
});

// Webhook (sans authentification, mais avec signature)
Route::post('/mobile-money/webhook/{provider}', [PaymentController::class, 'webhook']);
```

### 3.5 Base de Données

#### Migration : `create_mobile_money_transactions_table.php`

```php
Schema::create('mobile_money_transactions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->enum('provider', ['orange', 'mtn', 'moov']);
    $table->decimal('amount', 10, 2);
    $table->string('phone_number');
    $table->string('transaction_id')->unique();
    $table->string('reference'); // ID de la réservation/commande
    $table->enum('type', ['WALLET_RECHARGE', 'RIDE_PAYMENT']);
    $table->enum('status', ['PENDING', 'SUCCESS', 'FAILED', 'CANCELLED']);
    $table->string('provider_response')->nullable(); // JSON
    $table->text('error_message')->nullable();
    $table->timestamp('processed_at')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'status']);
    $table->index('transaction_id');
});
```

### 3.6 Sécurité

#### Middleware de Signature : `app/Http/Middleware/VerifyMobileMoneySignature.php`

```php
class VerifyMobileMoneySignature {
    public function handle($request, Closure $next) {
        $signature = $request->header('X-Signature');
        $payload = $request->getContent();
        
        $expectedSignature = hash_hmac('sha256', $payload, config('mobile_money.webhook_secret'));
        
        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        
        return $next($request);
    }
}
```

#### Journalisation : `app/Services/MobileMoneyLogService.php`

```php
class MobileMoneyLogService {
    public static function log($level, $message, $context = []) {
        Log::channel('mobile_money')->$level($message, [
            'timestamp' => now(),
            'context' => $context,
        ]);
    }
}
```

### 3.7 Application Android

#### Interfaces Utilisateur

1. **Recharger le portefeuille**
   - Sélection du fournisseur (Orange/MTN/Moov)
   - Saisie du montant
   - Saisie du numéro de téléphone
   - Confirmation et paiement

2. **Payer une course**
   - Sélection Mobile Money dans les options de paiement
   - Sélection du fournisseur
   - Confirmation et paiement
   - Suivi du statut

3. **Historique des transactions**
   - Liste des transactions Mobile Money
   - Filtres par statut, fournisseur
   - Détails de chaque transaction

---

## 4. Plan d'Implémentation par Phases

### Phase 1 : Infrastructure de Base (Semaines 1-2)
- [ ] Configuration des environnements de développement
- [ ] Installation des dépendances (web3.php, etc.)
- [ ] Création des migrations de base de données
- [ ] Configuration des variables d'environnement

### Phase 2 : Token ECO (Semaines 3-4)
- [ ] Développement du contrat ERC-20
- [ ] Tests du contrat
- [ ] Déploiement sur testnet
- [ ] API Laravel pour les tokens
- [ ] Intégration dans l'app Android

### Phase 3 : Mobile Money (Semaines 5-7)
- [ ] Intégration Orange Money
- [ ] Intégration MTN Mobile Money
- [ ] Intégration Moov Money (si disponible)
- [ ] Système de webhooks
- [ ] Tests de sécurité
- [ ] Intégration dans l'app Android

### Phase 4 : DAO (Semaines 8-10)
- [ ] Développement du contrat de gouvernance
- [ ] Tests du contrat
- [ ] Déploiement sur testnet
- [ ] API Laravel pour les propositions
- [ ] Intégration dans l'app Android

### Phase 5 : Tests et Optimisation (Semaines 11-12)
- [ ] Tests d'intégration complets
- [ ] Tests de sécurité
- [ ] Optimisation des performances
- [ ] Documentation finale
- [ ] Déploiement en production

---

## 5. Sécurité

### 5.1 Bonnes Pratiques

1. **Gestion des Clés**
   - Utiliser des variables d'environnement
   - Ne jamais commiter les clés
   - Rotation régulière des clés

2. **Validation des Transactions**
   - Vérifier les signatures
   - Valider les montants
   - Vérifier les statuts

3. **Journalisation**
   - Logger toutes les transactions
   - Logger les erreurs
   - Surveiller les activités suspectes

4. **Rate Limiting**
   - Limiter les appels API
   - Protection contre les abus
   - Monitoring des tentatives

---

## 6. Documentation Technique

### 6.1 Endpoints API

Voir le fichier `docs/api-endpoints.md` pour la documentation complète des endpoints.

### 6.2 Contrats Intelligents

Voir le dossier `contracts/` pour les contrats Solidity complets.

### 6.3 Guides d'Intégration

- Guide d'intégration Orange Money
- Guide d'intégration MTN Mobile Money
- Guide d'intégration Web3

---

## 7. Support et Maintenance

### 7.1 Monitoring

- Dashboard de monitoring des transactions
- Alertes en cas d'erreurs
- Rapports de performance

### 7.2 Support

- Documentation utilisateur
- FAQ
- Support technique

