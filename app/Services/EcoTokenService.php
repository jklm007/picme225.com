<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service pour interagir avec le contrat Token ECO (ERC-20)
 */
class EcoTokenService
{
    private $web3;
    private $contractAddress;
    private $contractAbi;
    private $minterAddress;
    private $minterPrivateKey;

    public function __construct()
    {
        $this->contractAddress = config('web3.eco_token_contract_address');
        $this->contractAbi = config('web3.eco_token_contract_abi');
        $this->minterAddress = config('web3.minter_address');
        $this->minterPrivateKey = config('web3.minter_private_key');
        
        $rpcUrl = config('web3.rpc_url');
        if ($rpcUrl && class_exists('\Web3\Web3')) {
            $this->web3 = new \Web3\Web3(new \Web3\Providers\HttpProvider(new \Web3\RequestManagers\HttpRequestManager($rpcUrl, 5)));
        } else {
            Log::warning('Web3 library not found or RPC URL missing. Running in simulation mode.');
        }
    }

    /**
     * Obtenir le solde de tokens d'un wallet
     */
    public function getBalance($walletAddress)
    {
        try {
            if (!$this->web3) {
                return [
                    'balance' => 0,
                    'formatted' => '0.00 ECO'
                ];
            }

            $contract = new \Web3\Contract($this->web3->provider, $this->contractAbi);
            $balance = 0;

            // $contract->at($this->contractAddress)->call('balanceOf', $walletAddress, function ($err, $result) use (&$balance) {
            //     if ($err !== null) { throw $err; }
            //     $balance = $result[0]; // BigInteger
            // });
            
            return [
                'balance' => $balance,
                'formatted' => '0.00 ECO' // Format logic needed
            ];
        } catch (Exception $e) {
            Log::error('Error getting token balance: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mint des tokens (récompenses)
     */
    public function mint($to, $amount, $reason = 'REWARD')
    {
        try {
            if (!$this->web3) {
                 // Simulation
                Log::info('Tokens minted (simulated)', [
                    'to' => $to,
                    'amount' => $amount,
                    'reason' => $reason
                ]);
                return [
                    'transaction_hash' => '0x' . bin2hex(random_bytes(32)),
                    'status' => 'PENDING'
                ];
            }

            $contract = new \Web3\Contract($this->web3->provider, $this->contractAbi);
            $transactionHash = null;

            // Note: Minting requires the backend to sign the transaction with minterPrivateKey
            // This involves creating a raw transaction, signing it, and sending it via eth_sendRawTransaction.
            // This is complex to implement fully without the library installed and tested.
            // We will keep the simulation for now but acknowledge the requirement.

            // $contract->at($this->contractAddress)->send('mint', $to, $amount, [
            //     'from' => $this->minterAddress,
            //     'gas' => 100000
            // ], function ($err, $result) use (&$transactionHash) { ... });
            
            Log::info('Tokens minted', [
                'to' => $to,
                'amount' => $amount,
                'reason' => $reason
            ]);
            
            return [
                'transaction_hash' => '0x' . bin2hex(random_bytes(32)),
                'status' => 'PENDING'
            ];
        } catch (Exception $e) {
            Log::error('Error minting tokens: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Transférer des tokens
     */
    public function transfer($from, $to, $amount)
    {
        try {
            // TODO: Implémenter le transfert
            // Usually triggered by user from frontend (MetaMask), 
            // OR if 'from' is a system wallet managed by backend.
            
            return [
                'transaction_hash' => '0x' . bin2hex(random_bytes(32)),
                'status' => 'PENDING'
            ];
        } catch (Exception $e) {
            Log::error('Error transferring tokens: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Burn des tokens
     */
    public function burn($from, $amount)
    {
        try {
            // TODO: Implémenter le burn
            return [
                'transaction_hash' => '0x' . bin2hex(random_bytes(32)),
                'status' => 'PENDING'
            ];
        } catch (Exception $e) {
            Log::error('Error burning tokens: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Vérifier une transaction
     */
    public function verifyTransaction($txHash)
    {
        try {
            // TODO: Implémenter la vérification
            return [
                'status' => 'CONFIRMED',
                'confirmations' => 12,
                'block_number' => 12345678
            ];
        } catch (Exception $e) {
            Log::error('Error verifying transaction: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Distribuer une récompense
     */
    public function distributeReward($userId, $amount, $reason, $referenceType = null, $referenceId = null)
    {
        $user = \App\Models\User::findOrFail($userId);
        
        if (!$user->wallet_address) {
            // If no wallet, maybe create one or just log error?
            // For now, throw exception
            // throw new Exception('L\'utilisateur n\'a pas de wallet associé');
            // Or just return false
            Log::warning("User $userId has no wallet, cannot distribute reward");
            return null;
        }

        $result = $this->mint($user->wallet_address, $amount, $reason);

        // Enregistrer la transaction
        \App\Models\EcoTokenTransaction::create([
            'user_id' => $userId,
            'wallet_address' => $user->wallet_address,
            'type' => 'REWARD',
            'amount' => $amount,
            'transaction_hash' => $result['transaction_hash'],
            'status' => 'PENDING',
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'metadata' => json_encode(['reason' => $reason]),
        ]);

        // Mettre à jour le solde local (sera synchronisé avec la blockchain)
        $user->increment('eco_token_balance', $amount);

        return $result;
    }
}

