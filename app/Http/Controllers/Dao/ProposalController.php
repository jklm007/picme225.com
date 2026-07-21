<?php

namespace App\Http\Controllers\Dao;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\DaoProposal;
use App\Services\Web3Service;
use Carbon\Carbon;

class ProposalController extends Controller
{
    private $web3Service;

    public function __construct(Web3Service $web3Service)
    {
        $this->web3Service = $web3Service;
    }

    /**
     * Liste des propositions
     * GET /api/dao/proposals
     */
    public function index(Request $request)
    {
        $query = DaoProposal::with(['proposer', 'votes']);

        // Filtres
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $proposals = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($proposals, 200);
    }

    /**
     * Détails d'une proposition
     * GET /api/dao/proposals/{id}
     */
    public function show($id)
    {
        $proposal = DaoProposal::with(['proposer', 'votes.user'])
            ->findOrFail($id);

        return response()->json($proposal, 200);
    }

    /**
     * Créer une proposition
     * POST /api/dao/proposals
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:PRICE_CHANGE,ROUTE_ADDITION,ROUTE_MODIFICATION,PARAMETER_CHANGE,STOP_ADDITION',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'execution_data' => 'nullable|array',
            'voting_period_days' => 'nullable|integer|min:1|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            // Check which guard is active
            $user = Auth::guard('api')->user();
            $creatorType = 'USER';
            if (!$user) {
                $user = Auth::guard('providerapi')->user();
                $creatorType = 'PROVIDER';
            }

            if (!$user) {
                return response()->json(['error' => 'Non autorisé'], 401);
            }

            // Vérifier que l'utilisateur a un wallet (ou le générer dynamiquement)
            if (!$user->wallet_address) {
                $user->wallet_address = '0x' . strtoupper(substr(md5(($creatorType ?? 'USER') . '_' . $user->id), 0, 40));
                $user->save();
            }

            // Gestion spécifique pour l'ajout d'arrêt
            if ($request->type === 'STOP_ADDITION') {
                $stopValidator = Validator::make($request->execution_data ?? [], [
                    'latitude' => 'required|numeric',
                    'longitude' => 'required|numeric',
                    'name' => 'required|string',
                    'commune' => 'required|string',
                ]);

                if ($stopValidator->fails()) {
                    return response()->json(['error' => $stopValidator->errors()], 422);
                }

                // Créer l'arrêt en statut PENDING
                $stop = \App\Models\PdpStop::create([
                    'name' => $request->execution_data['name'],
                    'latitude' => $request->execution_data['latitude'],
                    'longitude' => $request->execution_data['longitude'],
                    'commune' => $request->execution_data['commune'],
                    'status' => 'PENDING',
                    'is_public' => false,
                    'creator_id' => $user->id,
                    'creator_type' => 'USER', // Ou 'PROVIDER' selon le guard, ici User
                    'is_active' => true // Actif mais Pending, donc pas encore visible publiquement comme "Approuvé"
                ]);
                
                // On met à jour execution_data pour inclure l'ID
                $request->merge(['execution_data' => array_merge($request->execution_data, ['stop_id' => $stop->id])]);
            }

            // Créer la proposition sur la blockchain
            $blockchainResult = $this->web3Service->createProposal(
                $request->type,
                $request->title,
                $request->description,
                $request->execution_data ?? [],
                $user->wallet_address
            );

            // Enregistrer en base de données
            $votingPeriod = $request->voting_period_days ?? \Setting::get('dao_voting_period_days', 7);
            $proposal = DaoProposal::create([
                'blockchain_proposal_id' => $blockchainResult['proposal_id'],
                'user_id' => $user->id,
                'creator_type' => $creatorType,
                'type' => $request->type,
                'title' => $request->title,
                'description' => $request->description,
                'execution_data' => $request->execution_data,
                'status' => 'ACTIVE',
                'start_time' => Carbon::now(),
                'end_time' => Carbon::now()->addDays($votingPeriod),
            ]);

            return response()->json([
                'message' => 'Proposition créée avec succès',
                'proposal' => $proposal->load('proposer')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating proposal: ' . $e->getMessage());
            return response()->json([
                'error' => 'Une erreur est survenue lors de la création de la proposition'
            ], 500);
        }
    }

    /**
     * Voter sur une proposition
     * POST /api/dao/proposals/{id}/vote
     */
    public function vote(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'vote' => 'required|in:FOR,AGAINST,ABSTAIN',
            'votes_count' => 'nullable|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            // Check active guard (Client or Provider/Driver)
            $user = Auth::guard('api')->user();
            $voterType = 'USER';
            if (!$user) {
                $user = Auth::guard('providerapi')->user();
                $voterType = 'PROVIDER';
            }

            if ($user instanceof \App\Models\Provider) {
                $voterType = 'PROVIDER';
            }

            if (!$user) {
                return response()->json(['error' => 'Non autorisé'], 401);
            }

            $proposal = DaoProposal::findOrFail($id);

            // Vérifier que la proposition est active
            if (!$proposal->isActive()) {
                return response()->json([
                    'error' => 'Cette proposition n\'est plus active'
                ], 400);
            }

            // Vérifier que l'utilisateur n'a pas déjà voté (en prenant en compte le type de votant)
            $existingVote = $proposal->votes()
                ->where('user_id', $user->id)
                ->where('voter_type', $voterType)
                ->first();
            if ($existingVote) {
                return response()->json([
                    'error' => 'Vous avez déjà voté sur cette proposition'
                ], 400);
            }

            if (!$user->wallet_address) {
                $user->wallet_address = '0x' . strtoupper(substr(md5($voterType . '_' . $user->id), 0, 40));
                $user->save();
            }

            // [V2.3] Vote Quadratique (Anti-Whale)
            // Le coût en ECO augmente de manière quadratique avec le nombre de votes
            // Formule : Coût = (Votes)² × PrixBase (0.1 ECO)
            $votesCount = (int) $request->input('votes_count', 1);
            $tokenAmount = pow($votesCount, 2) * 0.1;
            
            // [V2.3] Déterminer dynamiquement la colonne de solde ECO appropriée
            // Le Chauffeur utilise eco_wallet_balance, le Client utilise eco_token_balance.
            $balanceField = 'eco_token_balance';
            if ($user instanceof \App\Models\Provider) {
                $balanceField = 'eco_wallet_balance';
            }
            
            $balance = (float) ($user->$balanceField ?? 0);
            if ($balance < $tokenAmount) {
                return response()->json([
                    'error' => "Solde ECO insuffisant. Pour exprimer {$votesCount} votes, le coût quadratique est de {$tokenAmount} ECO."
                ], 400);
            }

            $user->decrement($balanceField, $tokenAmount);

            $blockchainResult = $this->web3Service->vote(
                $proposal->blockchain_proposal_id,
                $request->vote === 'FOR',
                $user->wallet_address,
                $tokenAmount // On envoie le montant brulé/staké au smart contract
            );

            // Enregistrer le vote
            $vote = $proposal->votes()->create([
                'user_id' => $user->id,
                'voter_type' => $voterType,
                'user_wallet_address' => $user->wallet_address,
                'vote' => $request->vote,
                'token_amount' => $tokenAmount,
                'transaction_hash' => $blockchainResult['transaction_hash'],
                'status' => 'PENDING',
                'votes_weight' => $votesCount // On trace le poids réel
            ]);

            // Mettre à jour les compteurs (Poids du vote et non le token dépensé)
            if ($request->vote === 'FOR') {
                $proposal->increment('votes_for', $votesCount);
            } elseif ($request->vote === 'AGAINST') {
                $proposal->increment('votes_against', $votesCount);
            } else {
                $proposal->increment('votes_abstain', $votesCount);
            }

            return response()->json([
                'message' => 'Vote enregistré avec succès',
                'vote' => $vote
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error voting: ' . $e->getMessage());
            return response()->json([
                'error' => 'Une erreur est survenue lors du vote'
            ], 500);
        }
    }

    /**
     * Exécuter une proposition approuvée
     * POST /api/dao/proposals/{id}/execute
     */
    public function execute($id)
    {
        try {
            $proposal = DaoProposal::findOrFail($id);
            $governanceService = new \App\Services\DaoGovernanceService();
            
            $result = $governanceService->execute($proposal);

            if ($result['status'] === 'success') {
                return response()->json([
                    'message' => $result['message'],
                    'proposal' => $proposal
                ], 200);
            } else {
                return response()->json(['error' => $result['message']], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error executing proposal API: ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de l\'exécution'], 500);
        }
    }
}

