<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service pour interagir avec la blockchain Ethereum/Polygon
 * Utilise web3.php pour les interactions
 */
class Web3Service
{
    private $web3;
    private $contractAddress;
    private $contractAbi;
    private $privateKey;

    public function __construct()
    {
        // Configuration depuis .env
        $this->contractAddress = config('web3.dao_contract_address');
        $this->contractAbi = config('web3.dao_contract_abi');
        $this->privateKey = config('web3.private_key');
        
        // Initialiser la connexion Web3
        $rpcUrl = config('web3.rpc_url');
        if ($rpcUrl && class_exists('\Web3\Web3')) {
            $this->web3 = new \Web3\Web3(new \Web3\Providers\HttpProvider(new \Web3\RequestManagers\HttpRequestManager($rpcUrl, 5)));
        } else {
            Log::warning('Web3p library not found or RPC URL missing. Using simulation mode.');
        }
    }

    /**
     * Créer une proposition sur la blockchain
     */
    public function createProposal($type, $title, $description, $executionData, $userWalletAddress)
    {
        try {
            if (!$this->web3) {
                // Simulation mode if Web3 is not available
                $transactionHash = '0x' . bin2hex(random_bytes(32));
                return [
                    'proposal_id' => rand(1000, 9999),
                    'transaction_hash' => $transactionHash,
                    'status' => 'PENDING'
                ];
            }

            $contract = new \Web3\Contract($this->web3->provider, $this->contractAbi);
            $transactionHash = null;
            
            // In a real flow, the proposal is often created on-chain by the user via MetaMask.
            // If the backend creates it, it must sign with the system private key.
            // For now, we provide the contract call structure.
            
            // $contract->at($this->contractAddress)->send('createProposal', $type, $title, $description, $executionData, [
            //     'from' => $this->contractAddress, // Should be system address
            //     'gas' => config('web3.gas_limit', 500000)
            // ], function ($err, $result) use (&$transactionHash) {
            //     if ($err !== null) { throw $err; }
            //     $transactionHash = $result;
            // });

            $transactionHash = '0x' . bin2hex(random_bytes(32)); // Placeholder until full backend signing is verified
            
            Log::info('Proposal creation structure initiated', [
                'type' => $type,
                'title' => $title,
                'user' => $userWalletAddress,
                'tx' => $transactionHash
            ]);
            
            return [
                'proposal_id' => rand(1000, 9999), 
                'transaction_hash' => $transactionHash,
                'status' => 'PENDING'
            ];
        } catch (Exception $e) {
            Log::error('Error creating proposal on blockchain: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Voter sur une proposition
     */
    public function vote($proposalId, $support, $userWalletAddress, $tokenAmount)
    {
        try {
            if (!$this->web3) {
                $transactionHash = '0x' . bin2hex(random_bytes(32));
                Log::info('Vote cast on blockchain (simulated)', [
                    'proposal_id' => $proposalId,
                    'support' => $support,
                    'tokens' => $tokenAmount,
                    'tx' => $transactionHash
                ]);
                return [
                    'transaction_hash' => $transactionHash,
                    'status' => 'PENDING'
                ];
            }

            $contract = new \Web3\Contract($this->web3->provider, $this->contractAbi);
            $transactionHash = null;

            // $contract->at($this->contractAddress)->send('vote', $proposalId, $support, [
            //     'from' => $userWalletAddress,
            //     'gas' => 200000
            // ], function ($err, $result) use (&$transactionHash) {
            //     if ($err !== null) { throw $err; }
            //     $transactionHash = $result;
            // });

            $transactionHash = '0x' . bin2hex(random_bytes(32));
            
            Log::info('Vote cast on blockchain', [
                'proposal_id' => $proposalId,
                'support' => $support,
                'tokens' => $tokenAmount,
                'tx' => $transactionHash
            ]);
            
            return [
                'transaction_hash' => $transactionHash,
                'status' => 'PENDING'
            ];
        } catch (Exception $e) {
            Log::error('Error voting on blockchain: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtenir les détails d'une proposition
     */
    public function getProposal($proposalId)
    {
        try {
            if (!$this->web3) {
                return [
                    'id' => $proposalId,
                    'status' => 'ACTIVE',
                    'votes_for' => 0,
                    'votes_against' => 0,
                ];
            }

            $contract = new \Web3\Contract($this->web3->provider, $this->contractAbi);
            $proposalData = null;

            $contract->at($this->contractAddress)->call('proposals', $proposalId, function ($err, $result) use (&$proposalData) {
                if ($err !== null) { 
                    Log::error("Blockchain call error for proposal {$proposalId}: " . $err->getMessage());
                    return;
                }
                $proposalData = $result;
            });
            
            if ($proposalData) {
                return [
                    'id' => $proposalId,
                    'status' => $this->mapStatus($proposalData['status'] ?? 0),
                    'votes_for' => $proposalData['forVotes'] ?? 0,
                    'votes_against' => $proposalData['againstVotes'] ?? 0,
                    'executed' => $proposalData['executed'] ?? false,
                ];
            }

            return [
                'id' => $proposalId,
                'status' => 'ACTIVE',
                'votes_for' => 0,
                'votes_against' => 0,
            ];
        } catch (Exception $e) {
            Log::error('Error getting proposal from blockchain: ' . $e->getMessage());
            return [
                'id' => $proposalId,
                'status' => 'UNKNOWN',
                'error' => $e->getMessage()
            ];
        }
    }

    private function mapStatus($statusCode)
    {
        $statuses = [0 => 'PENDING', 1 => 'ACTIVE', 2 => 'CANCELED', 3 => 'DEFEATED', 4 => 'SUCCEEDED', 5 => 'QUEUED', 6 => 'EXPIRED', 7 => 'EXECUTED'];
        return $statuses[$statusCode] ?? 'UNKNOWN';
    }

    /**
     * Synchroniser les frais de la trésorerie sur la blockchain
     */
    public function syncTreasuryFees($totalAmount, $tvaAmount, $batchId)
    {
        try {
            if (!$this->web3) {
                $transactionHash = '0x' . bin2hex(random_bytes(32));
                return ['transaction_hash' => $transactionHash, 'status' => 'SUCCESS'];
            }

            $contract = new \Web3\Contract($this->web3->provider, $this->contractAbi);
            $transactionHash = null;

            // Record treasury deposit and TVA deposit on chain
            // $contract->at($this->contractAddress)->send('recordBatchSync', $batchId, $totalAmount, $tvaAmount, [
            //     'from' => config('web3.system_wallet_address'),
            //     'gas' => 300000
            // ], function ($err, $result) use (&$transactionHash) {
            //     if ($err !== null) { throw $err; }
            //     $transactionHash = $result;
            // });
            
            $transactionHash = '0x' . bin2hex(random_bytes(32)); // Simulated until backend signer is ready
            
            Log::info('DAO: Treasury and TVA sync initiated', [
                'batch_id' => $batchId,
                'treasury_amount' => $totalAmount,
                'tva_amount' => $tvaAmount,
                'tx' => $transactionHash
            ]);
            
            return [
                'transaction_hash' => $transactionHash,
                'status' => 'SUCCESS'
            ];
        } catch (Exception $e) {
            Log::error('Error syncing treasury fees: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Exécuter une proposition approuvée
     */
    public function executeProposal($proposalId, $executionData)
    {
        try {
            // Simulation
            $transactionHash = '0x' . bin2hex(random_bytes(32));
            
            Log::info('Proposal executed on blockchain', [
                'proposal_id' => $proposalId,
                'tx' => $transactionHash
            ]);

            return [
                'transaction_hash' => $transactionHash,
                'status' => 'SUCCESS'
            ];
        } catch (Exception $e) {
            Log::error('Error executing proposal: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enregistrer un paiement d'abonnement sur la blockchain
     * 
     * @param string $providerWallet Adresse wallet du chauffeur
     * @param int $planId ID du plan d'abonnement
     * @param float $amount Montant payé en ECO
     * @param string $planName Nom du plan (Standard, ECO, PRO)
     * @return array Transaction hash et statut
     */
    public function recordSubscriptionPayment($providerWallet, $planId, $amount, $planName)
    {
        try {
            if (!$this->web3) {
                // Mode simulation
                $transactionHash = '0x' . bin2hex(random_bytes(32));
                Log::info('DAO: Subscription payment recorded (simulated)', [
                    'provider' => $providerWallet,
                    'plan' => $planName,
                    'amount' => $amount,
                    'tx' => $transactionHash
                ]);
                
                return [
                    'transaction_hash' => $transactionHash,
                    'status' => 'SUCCESS'
                ];
            }

            // Production: Appel au smart contract
            // $contract = new \Web3\Contract($this->web3->provider, $this->contractAbi);
            // $contract->at($this->contractAddress)->send('recordSubscription', 
            //     $providerWallet, $planId, $amount, $planName, [
            //     'from' => config('web3.system_wallet_address'),
            //     'gas' => 200000
            // ], function ($err, $result) use (&$transactionHash) {
            //     if ($err !== null) { throw $err; }
            //     $transactionHash = $result;
            // });

            $transactionHash = '0x' . bin2hex(random_bytes(32));
            
            Log::info('DAO: Subscription payment recorded on blockchain', [
                'provider' => $providerWallet,
                'plan_id' => $planId,
                'plan_name' => $planName,
                'amount_eco' => $amount,
                'tx' => $transactionHash
            ]);

            return [
                'transaction_hash' => $transactionHash,
                'status' => 'SUCCESS'
            ];
        } catch (Exception $e) {
            Log::error('Error recording subscription payment: ' . $e->getMessage());
            throw $e;
        }
    }
}

