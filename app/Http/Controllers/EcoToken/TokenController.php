<?php

namespace App\Http\Controllers\EcoToken;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\EcoTokenTransaction;
use App\Services\EcoTokenService;

class TokenController extends Controller
{
    private $ecoTokenService;

    public function __construct(EcoTokenService $ecoTokenService)
    {
        $this->ecoTokenService = $ecoTokenService;
    }

    /**
     * Obtenir le solde de tokens
     * GET /api/eco-token/balance
     */
    public function balance(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user->wallet_address) {
                return response()->json([
                    'balance' => 0,
                    'wallet_address' => null,
                    'message' => 'Aucun wallet associé'
                ], 200);
            }

            // Obtenir le solde depuis la blockchain
            $blockchainBalance = $this->ecoTokenService->getBalance($user->wallet_address);

            return response()->json([
                'balance' => $user->eco_token_balance,
                'blockchain_balance' => $blockchainBalance['balance'],
                'wallet_address' => $user->wallet_address,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting token balance: ' . $e->getMessage());
            return response()->json([
                'error' => 'Une erreur est survenue'
            ], 500);
        }
    }

    /**
     * Historique des transactions
     * GET /api/eco-token/transactions
     */
    public function transactions(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            $transactions = EcoTokenTransaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json($transactions, 200);

        } catch (\Exception $e) {
            Log::error('Error getting transactions: ' . $e->getMessage());
            return response()->json([
                'error' => 'Une erreur est survenue'
            ], 500);
        }
    }

    /**
     * Transférer des tokens
     * POST /api/eco-token/transfer
     */
    public function transfer(Request $request)
    {
        // ══════════════════════════════════════════════════════════════════
        // CONFORMITÉ UEMOA/BCEAO — Les transferts P2P de tokens ECO entre
        // particuliers sont INTERDITS. L'ECO est une monnaie interne de
        // plateforme (boucle fermée). Seul le wallet système peut recevoir.
        // ══════════════════════════════════════════════════════════════════
        return response()->json([
            'error' => 'Les transferts directs d\'ECO entre utilisateurs ne sont pas autorisés (conformité BCEAO/UEMOA). Utilisez le paiement de services via /api/eco-token/pay.'
        ], 403);
    }

    /**
     * Payer avec des tokens
     * POST /api/eco-token/pay
     */
    public function payWithTokens(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'         => 'required|numeric|min:0.00000001',
            'reference_type' => 'required|string',
            'reference_id'   => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $userId = Auth::guard('api')->id();
            $result = null;

            \DB::transaction(function () use ($userId, $request, &$result) {
                // Verrou pessimiste — empêche la double dépense par race condition
                $user = \App\Models\User::where('id', $userId)->lockForUpdate()->first();

                if (!$user->wallet_address) {
                    throw new \Exception('Vous devez avoir un wallet associé.');
                }

                if ($user->eco_token_balance < $request->amount) {
                    throw new \Exception('Solde de tokens insuffisant.');
                }

                // Transférer vers le wallet système
                $systemWallet = config('web3.system_wallet_address');
                $txResult = $this->ecoTokenService->transfer(
                    $user->wallet_address,
                    $systemWallet,
                    $request->amount
                );

                // Enregistrer la transaction
                EcoTokenTransaction::create([
                    'user_id'        => $user->id,
                    'wallet_address' => $user->wallet_address,
                    'type'           => 'PAYMENT',
                    'amount'         => -$request->amount,
                    'transaction_hash' => $txResult['transaction_hash'],
                    'status'         => 'PENDING',
                    'reference_type' => $request->reference_type,
                    'reference_id'   => $request->reference_id,
                ]);

                // Décrémenter le solde local de façon atomique
                $user->decrement('eco_token_balance', $request->amount);
                $result = $txResult;
            });

            return response()->json([
                'message'          => 'Paiement effectué avec succès',
                'transaction_hash' => $result['transaction_hash']
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error paying with tokens: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
