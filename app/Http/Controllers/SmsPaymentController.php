<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SmsPaymentController extends Controller
{
    /**
     * Reçoit les SMS transférés par l'application mobile (SMS Gateway).
     * Gère l'identification du Node, les volumes journaliers et les commissions hybrides.
     */
    public function handleSms(Request $request)
    {
        $sender = $request->input('from');
        $text = $request->input('message') ?? $request->input('text');
        $receiverPhone = $request->input('receiver_phone'); 

        if (!$text) {
            return response()->json(['status' => 'error', 'message' => 'No text provided'], 400);
        }

        Log::info("SMS RECU sur $receiverPhone de $sender : $text");

        $upperSender = strtoupper($sender);
        $detectedNetwork = 'MOOV';
        if (str_contains($upperSender, 'WAVE') || stripos($text, 'WAVE') !== false) {
            $detectedNetwork = 'WAVE';
        } elseif (str_contains($upperSender, 'ORANGE') || str_contains($upperSender, '+454') || preg_match('/(?:Reference|Ref)\s*PP/i', $text)) {
            $detectedNetwork = 'ORANGE';
        } elseif (str_contains($upperSender, 'MTN')) {
            $detectedNetwork = 'MTN';
        }

        $node = null;
        $realBalanceSynced = false;

        // --- 1. SYNCHRONISATION ABSOLUE DU SOLDE DU NODE ---
        if ($receiverPhone) {
            $node = \App\Models\GatewayNode::where('phone_number', 'like', "%$receiverPhone%")
                ->where('network', $detectedNetwork)
                ->first();
                
            if ($node) {
                preg_match('/(?:nouveau solde|solde actuel|solde|balance)[:\s]*([\d\s.,]+)(?:F|FCFA)/i', $text, $balanceMatches);
                if (!empty($balanceMatches[1])) {
                    // On remplace la virgule éventuelle par un point, et on ne garde que les chiffres et le point
                    $newBalanceStr = preg_replace('/[^\d.]/', '', str_replace(',', '.', $balanceMatches[1]));
                    if (!empty($newBalanceStr)) {
                        $newBalance = floatval($newBalanceStr);
                        $node->current_balance = $newBalance;
                        $node->save();
                        $realBalanceSynced = true;
                        Log::info("SYNC SOLDE RÉEL : Le Node {$node->name} a maintenant {$newBalance} FCFA selon le SMS de l'opérateur.");
                    }
                }
            }
        }
        // ---------------------------------------------------

        // On bloque les transferts sortants (retraits par l'admin) pour ne pas les confondre avec un dépôt client
        if (preg_match('/(?:transf[eé]r[eé]|envoy[eé])/i', $text) && !preg_match('/(?:re[cç]u|d[eé]p[oô]t)/i', $text)) {
            Log::info("SMS de transfert sortant ignoré pour les paiements (mais le solde a pu être mis à jour).");
            return response()->json(['status' => 'ignored', 'message' => 'Outgoing transfer SMS']);
        }

        preg_match('/(\+?225\s*|0)?([0157]\d{9})/', str_replace(' ', '', $text), $matches);
        $customerPhone = $matches[2] ?? null;

        $textWithoutSpaces = str_replace(' ', '', $text);
        
        // Extraction de l'ID de Transaction (ex: ID Transaction: MP23... ou Ref: CI23...)
        preg_match('/(?:IDTransaction|TransID|Reference|R[eé]f[eé]rence|Ref|R[eé]f|Txn|TransactionID|IDdeTransaction|ID)[:\-]*([A-Z0-9.]+)/i', $textWithoutSpaces, $txnMatches);
        $transactionId = !empty($txnMatches[1]) ? strtoupper(trim($txnMatches[1], '.')) : 'TX-' . time();

        // Extraction du montant améliorée (accepte de, depot, recu, transfert, paiement, montant)
        preg_match('/(?:re[cç]u|montant|de|d[eé]p[oô]t|transfert|paiement)[:\-]*(\d+(?:[.,]\d+)?)(?:F|FCFA|CFA)/i', $textWithoutSpaces, $amountMatches);
        $amount = $amountMatches[1] ?? null;

        if (!$amount) {
            // Fallback ultime : prend le premier montant trouvé qui se termine par F, FCFA, CFA
            preg_match('/(\d+(?:[.,]\d+)?)(?:F|FCFA|CFA)/i', $textWithoutSpaces, $amountMatches);
            $amount = $amountMatches[1] ?? null;
        }

        // Nettoyer le montant (enlever les virgules/points pour avoir l'entier)
        if ($amount) {
            $amount = floatval(str_replace(',', '.', $amount));
        }

        if ($customerPhone && $amount) {

            preg_match('/(?:ID\s*Transaction|Transaction\s*ID|Trans\s*ID|ID\s*de\s*Transaction|Reference|Référence|Ref|Réf|Txn)[:\s\-]*([A-Z0-9\.]+)/i', $text, $idMatches);
            if (!empty($idMatches[1])) {
                $transactionId = trim($idMatches[1], '.'); // Remove trailing dot if any
                $transactionId = strtoupper($transactionId);
            }
            if (empty($transactionId)) {
                $transactionId = 'TX-' . time();
            }

            // --- AUTO-VERIFICATION DU RECHARGEMENT (Mise  jour pour PicMe Pro) ---
            $pendingTransaction = \App\Models\MobileMoneyTransaction::where('phone_number', 'like', "%$customerPhone%")
                ->where('amount', $amount)
                ->where('status', 'PENDING')
                ->first();
            
            if ($pendingTransaction) {
                $pendingTransaction->status = 'SUCCESS';
                $pendingTransaction->transaction_id = $transactionId;
                $pendingTransaction->processed_at = now();
                $pendingTransaction->save();
            }
            // ----------------------------------------------------------------------

            $commission = $this->calculateCommission($amount);
            $amountToCredit = max(0, $amount - $commission);

            // --- MISE À JOUR CRITIQUE DES STATS DU NODE (Indépendant de l'utilisateur) ---
            if ($node) {
                $node->increment('daily_volume', $amount);
                $node->increment('monthly_volume', $amount);
                
                // Si on n'a pas pu extraire le solde réel du SMS, on incrémente virtuellement
                if (!$realBalanceSynced) {
                    $node->increment('current_balance', $amount);
                }

                // SǸparer les profits virtuels par rǸseau pour matcher l'argent physique
                $profitNode = \App\Models\GatewayNode::where('type', 'PROFIT')->where('network', $transaction->payment_method)->first();
                if ($profitNode) {
                    $profitNode->increment('current_balance', $commission);
                }

                if ($node->daily_volume >= ($node->daily_limit * 0.8)) {
                    Log::warning("⚠️ ALERTE PLAFOND: Le Node {$node->name} est à 80%.");
                }
            }

            $user = DB::table('users')->where('mobile', 'like', "%$customerPhone%")->first();
            if ($user) {
                $exists = DB::table('wallet_passbooks')->where('transaction_id', $transactionId)->whereNotNull('transaction_id')->exists();
                if ($exists) {
                    return response()->json(['status' => 'duplicate', 'message' => 'Transaction déjà traitée']);
                }

                DB::beginTransaction();
                try {
                    DB::table('users')->where('id', $user->id)->increment('wallet_balance', $amountToCredit);

                    DB::table('wallet_passbooks')->insert([
                        'user_id' => $user->id,
                        'amount' => $amountToCredit,
                        'status' => 'CREDITED',
                        'via' => 'MOBILE_MONEY',
                        'transaction_id' => $transactionId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // --- NOUVEAUTÉ : Réveiller PicMe Pro avec une notification Push ---
                    try {
                        $data = [
                            'type' => 'WALLET_CREDITED',
                            'amount' => $amountToCredit,
                            'transaction_id' => $transactionId,
                            'phone' => $customerPhone,
                            'message' => "Votre compte a été rechargé de " . $amountToCredit . " FCFA"
                        ];

                        $pushController = new \App\Http\Controllers\SendPushNotification();
                        $pushController->sendPushToUser($user->id, $data, 'Rechargement Validé');
                    } catch (\Exception $e) {
                        \Log::error('Erreur envoi Push Wallet Credited: ' . $e->getMessage());
                    }
                    // ------------------------------------------------------------------

                    DB::commit();
                    return response()->json(['status' => 'success', 'amount' => $amountToCredit, 'role' => 'USER', 'transaction_id' => $transactionId]);
                } catch (\Exception $e) {
                    DB::rollback();
                    Log::error("Erreur crédit auto User: " . $e->getMessage());
                }
            } else {
                $provider = DB::table('providers')->where('mobile', 'like', "%$customerPhone%")->first();
                if ($provider) {
                    $exists = DB::table('provider_wallets')->where('transaction_id', $transactionId)->whereNotNull('transaction_id')->exists();
                    if ($exists) {
                        return response()->json(['status' => 'duplicate', 'message' => 'Transaction déjà traitée']);
                    }

                    DB::beginTransaction();
                    try {
                        DB::table('providers')->where('id', $provider->id)->increment('wallet_balance', $amountToCredit);
                        
                        $updatedProvider = DB::table('providers')->where('id', $provider->id)->first();

                        DB::table('provider_wallets')->insert([
                            'provider_id' => $provider->id,
                            'amount' => $amountToCredit,
                            'type' => 'CREDIT',
                            'transaction_desc' => 'MOBILE_MONEY',
                            'transaction_id' => $transactionId,
                            'balance' => $updatedProvider->wallet_balance,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        // --- Réveiller PicMe Pro avec une notification Push ---
                        if (!empty($provider->device_token)) {
                            try {
                                $data = [
                                    'type' => 'WALLET_CREDITED',
                                    'amount' => $amountToCredit,
                                    'transaction_id' => $transactionId,
                                    'phone' => $customerPhone,
                                    'message' => "Votre compte a été rechargé de " . $amountToCredit . " FCFA"
                                ];
                                $pushController = new \App\Http\Controllers\SendPushNotification();
                                $pushController->sendPushToProvider($provider->id, $data, 'Rechargement Validé');
                            } catch (\Exception $e) {
                                \Log::error('Erreur envoi Push Wallet Credited: ' . $e->getMessage());
                            }
                        }
                        // ------------------------------------------------------------------

                        DB::commit();
                        return response()->json(['status' => 'success', 'amount' => $amountToCredit, 'role' => 'PROVIDER', 'transaction_id' => $transactionId]);
                    } catch (\Exception $e) {
                        DB::rollback();
                        Log::error("Erreur crédit auto Provider: " . $e->getMessage());
                    }
                }
            }
        }

        return response()->json(['status' => 'logged']);
    }

    /**
     * Liste des retraits intelligemment filtrés pour le Robot appelant.
     */
    public function getPendingPayouts(Request $request)
    {
        $robotPhone = $request->input('receiver_phone'); // Le numéro de la SIM du Robot
        
        if (!$robotPhone) {
            return response()->json(['status' => 'error', 'message' => 'Identification du robot manquante'], 400);
        }

        // Trouver les réseaux gérés par ce téléphone physique
        $nodes = \App\Models\GatewayNode::where('phone_number', 'like', "%$robotPhone%")
            ->where('status', 'ACTIVE')
            ->get();

        if ($nodes->isEmpty()) {
            return response()->json([]);
        }

        $networks = $nodes->pluck('network')->toArray();
        
        // Chercher un retrait en attente sur l'un de ces réseaux
        // ET dont le montant est inférieur au solde actuel du node
        $payout = \App\Models\MobileMoneyTransaction::where('type', 'WITHDRAWAL')
            ->where('status', 'PENDING')
            ->whereIn('network', $networks)
            ->where(function($query) use ($nodes) {
                foreach ($nodes as $node) {
                    $query->orWhere(function($q) use ($node) {
                        $q->where('network', $node->network)
                          ->where('amount', '<=', $node->current_balance);
                    });
                }
            })
            ->first(); // On en traite un par un pour plus de sécurité

        return response()->json($payout ? [$payout] : []);
    }

    /**
     * Confirmation de retrait par le Robot.
     */
    public function confirmPayout(Request $request)
    {
        $id = $request->input('transaction_id');
        $status = $request->input('status'); 
        $receiverPhone = $request->input('receiver_phone'); 

        $transaction = \App\Models\MobileMoneyTransaction::where('transaction_id', $id)->first();
        if ($transaction) {
            $transaction->status = $status;
            $transaction->processed_at = now();
            $transaction->save();

            if ($status == 'SUCCESS') {
                $amount = $transaction->amount;
                $feeProfit = $this->calculateCommission($amount);

                $node = \App\Models\GatewayNode::where('phone_number', 'like', "%$receiverPhone%")->first();
                if ($node) {
                    $node->decrement('current_balance', $amount);
                }

                // SǸparer les profits virtuels par rǸseau pour matcher l'argent physique
                $profitNode = \App\Models\GatewayNode::where('type', 'PROFIT')->where('network', strtoupper($transaction->payment_method))->first();
                if ($profitNode) {
                    $profitNode->increment('current_balance', $feeProfit);
                }
            }
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'error'], 404);
    }

    /**
     * Pour l'App Mobile : quel numéro donner au client ?
     */
    public function getActiveReceiver(Request $request)
    {
        $network = $request->input('network', 'WAVE');
        
        // 1. Priorité aux Nodes de type RECEIVER
        $node = \App\Models\GatewayNode::where('type', 'RECEIVER')
            ->where('network', $network)
            ->where('status', 'ACTIVE')
            ->whereRaw('daily_volume < daily_limit')
            ->whereRaw('monthly_volume < monthly_limit')
            ->orderBy('daily_volume', 'asc')
            ->first();

        // 2. BACKUP 1 : Si aucun récepteur, on utilise un PAYOUT comme secours
        if (!$node) {
            $node = \App\Models\GatewayNode::where('type', 'PAYOUT')
                ->where('network', $network)
                ->where('status', 'ACTIVE')
                ->whereRaw('daily_volume < daily_limit')
                ->whereRaw('monthly_volume < monthly_limit')
                ->orderBy('daily_volume', 'asc')
                ->first();
                
            if ($node) {
                Log::warning("⚠️ ROUTAGE DE SECOURS 1 : Utilisation du Node PAYOUT {$node->name}.");
            }
        }

        // 3. BACKUP 2 : Si même les payeurs sont pleins, on utilise le COFFRE (VAULT)
        if (!$node) {
            $node = \App\Models\GatewayNode::where('type', 'VAULT')
                ->where('network', $network)
                ->where('status', 'ACTIVE')
                ->whereRaw('daily_volume < daily_limit')
                ->whereRaw('monthly_volume < monthly_limit')
                ->orderBy('daily_volume', 'asc')
                ->first();
                
            if ($node) {
                Log::warning("⚠️ ROUTAGE DE SECOURS ULTIME : Utilisation du COFFRE {$node->name} !");
            }
        }

        if ($node) {
            return response()->json([
                'status' => 'success',
                'phone_number' => $node->phone_number,
                'name' => $node->name
            ]);
        }
        
        return response()->json(['status' => 'error', 'message' => 'Capacité maximale atteinte'], 404);
    }

    /**
     * SYSTÈME HYBRIDE COMPÉTITIF : 100 F fixe ou 2%
     */
    private function calculateCommission($amount)
    {
        if ($amount <= 5000) {
            return 100;
        } else {
            return $amount * 0.02;
        }
    }

    public function verifyManualRecharge(Request $request)
    {
        \Log::info('VERIFY RECHARGE APPELÉ', $request->all());
        return response()->json(['status' => 'manual_verify_endpoint']);
    }
}
