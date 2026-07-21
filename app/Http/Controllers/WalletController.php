<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletPassbook;
use Auth;
use Exception;
use Log;

class WalletController extends Controller
{
    public function rewardAdMob(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }

            // Reward amount for watching ad
            $rewardAmount = 0.1;

            $user->eco_wallet_balance += $rewardAmount;
            $user->save();

            // Enregistrer la transaction
            WalletPassbook::create([
                'user_id' => $user->id,
                'amount' => $rewardAmount,
                'status' => 'CREDITED',
                'via' => 'AdMob Reward Video',
            ]);

            return response()->json([
                'message' => 'Félicitations ! Vous avez gagné '.$rewardAmount.' ECO.',
                'eco_wallet_balance' => $user->eco_wallet_balance
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Recharger le portefeuille de l'utilisateur.
     * Simulation de paiement Mobile Money / Carte.
     */
    public function add_money(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:100'
        ]);

        $gateway = Setting::get('payment_gateway', config('services.payment_gateway', 'MANUAL'));

        if ($gateway === 'CINETPAY') {
            return $this->processCinetPay($request);
        } else {
            return $this->processManualPayment($request);
        }
    }

    /**
     * Traitement du paiement via CinetPay
     */
    private function processCinetPay(Request $request)
    {
        try {
            $user = Auth::user();
            $amount = $request->amount;
            $transaction_id = 'CP-' . uniqid();

            $siteId  = \Setting::get('cinetpay_site_id', config('services.cinetpay.site_id'));
            $apiKey  = \Setting::get('cinetpay_api_key', config('services.cinetpay.apikey'));

            WalletPassbook::create([
                'user_id'          => $user->id,
                'amount'           => $amount,
                'status'           => 'PENDING',
                'via'              => 'CINETPAY',
                'transaction_desc' => $transaction_id
            ]);

            // URL CinetPay v2 avec tous les paramètres obligatoires
            $payment_url = 'https://checkout.cinetpay.com/v2/?' . http_build_query([
                'apikey'         => $apiKey,
                'site_id'        => $siteId,
                'transaction_id' => $transaction_id,
                'amount'         => $amount,
                'currency'       => 'XOF',
                'description'    => 'Recharge portefeuille PicMe225',
                'return_url'     => config('app.url') . '/payment/return?ref=' . $transaction_id,
                'notify_url'     => config('app.url') . '/api/wallet/webhook/cinetpay',
            ]);

            return response()->json([
                'success'        => true,
                'message'        => 'Redirection vers CinetPay.',
                'payment_url'    => $payment_url,
                'transaction_id' => $transaction_id,
            ]);
        } catch (Exception $e) {
            Log::error("Erreur génération CinetPay : " . $e->getMessage());
            return response()->json(['error' => 'Erreur CinetPay.'], 500);
        }
    }

    /**
     * Traitement Manuel / Wave (Ancien comportement)
     */
    private function processManualPayment(Request $request)
    {
        try {
            $user   = Auth::user();
            $amount = $request->amount;
            $network = strtoupper($request->input('network', 'WAVE'));

            // Anti-doublon : empêche plusieurs tentatives en moins de 2 minutes
            $recentAttempt = \App\Models\WalletPassbook::where('user_id', $user->id)
                ->where('status', 'PENDING')
                ->where('created_at', '>=', now()->subMinutes(2))
                ->count();

            if ($recentAttempt >= 1) {
                return response()->json([
                    'error' => 'Une demande est déjà en cours. Veuillez patienter 2 minutes.'
                ], 429);
            }

            // Trouver le numéro de réception actif via le Gateway Hub
            $node = \App\Models\GatewayNode::where('type', 'RECEIVER')
                ->where('network', $network)
                ->where('status', 'ACTIVE')
                ->whereRaw('daily_volume < daily_limit')
                ->orderBy('daily_volume', 'asc')
                ->first();

            if (!$node) {
                $node = \App\Models\GatewayNode::where('network', $network)
                    ->where('status', 'ACTIVE')
                    ->whereRaw('daily_volume < daily_limit')
                    ->orderBy('daily_volume', 'asc')
                    ->first();
            }

            if (!$node) {
                return response()->json([
                    'error' => 'Aucun numéro de réception disponible pour ' . $network . ' actuellement. Essayez un autre réseau ou réessayez dans quelques minutes.'
                ], 503);
            }

            // Enregistrer la tentative comme PENDING (sera mise à jour par le robot SMS)
            \App\Models\WalletPassbook::create([
                'user_id' => $user->id,
                'amount'  => $amount,
                'status'  => 'PENDING',
                'via'     => $network,
                'transaction_desc' => 'MANUAL_' . $node->phone_number . '_' . time(),
            ]);

            return response()->json([
                'success'        => true,
                'message'        => 'Envoyez exactement ' . $amount . ' CFA au numéro ci-dessous. Votre compte sera rechargé automatiquement dès confirmation.',
                'receiver_name'  => $node->name,
                'receiver_phone' => $node->phone_number,
                'network'        => $network,
                'amount'         => $amount,
                'instructions'   => 'Référence : PicMe-' . $user->id . '-' . $amount,
                'mode'           => 'MANUAL_GATEWAY',
            ]);

        } catch (Exception $e) {
            Log::error("Erreur Gateway Manuel User : " . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue.'], 500);
        }
    }

    /**
     * Webhook Sécurisé pour la réception du paiement Wave
     */
    public function waveWebhook(Request $request)
    {
        // ── VALIDATION CRYPTOGRAPHIQUE DE LA SIGNATURE WAVE ─────────────────
        $signature = $request->header('Wave-Signature');
        $secret    = config('services.wave.webhook_secret');

        if ($secret && $signature) {
            $expectedSig = hash_hmac('sha256', $request->getContent(), $secret);
            if (!hash_equals($signature, $expectedSig)) {
                Log::warning('Wave Webhook : signature invalide reçue.');
                return response()->json(['error' => 'Signature de webhook invalide.'], 401);
            }
        } elseif ($secret && !$signature) {
            // Si on a un secret configuré mais pas de signature : rejeter
            return response()->json(['error' => 'Signature Wave manquante.'], 401);
        }
        // ────────────────────────────────────────────────────────────────────

        $type = $request->input('type');
        if ($type === 'checkout.session.completed') {
            $data = $request->input('data');
            // 'client_reference' mapped to our transaction_desc
            $sessionId = $data['client_reference'] ?? $request->input('session_id');

            try {
                \DB::transaction(function() use ($sessionId) {
                    // Verrouiller la transaction PENDING
                    $passbook = WalletPassbook::where('transaction_desc', $sessionId)
                        ->where('status', 'PENDING')
                        ->lockForUpdate()
                        ->first();
                        
                    if ($passbook) {
                        $passbook->status = 'CREDITED';
                        $passbook->save();
                        
                        // Verrouiller l'utilisateur et créditer (Anti-fraude)
                        $lockedUser = User::where('id', $passbook->user_id)->lockForUpdate()->first();
                        $lockedUser->wallet_balance += $passbook->amount;
                        $lockedUser->save();

                        // Optionnel : Notifier via SendFirebasePushJob
                        \App\Jobs\SendFirebasePushJob::dispatch(
                            $lockedUser->device_token, 
                            ['type' => 'wallet_credited'], 
                            "Paiement Wave Reçu", 
                            "Votre compte a été rechargé de " . $passbook->amount . " FCFA"
                        );
                    }
                });
            } catch (Exception $e) {
                Log::error("Wave Webhook Transaction Error: " . $e->getMessage());
                return response()->json(['error' => 'Transaction Failed'], 500);
            }
        }
        return response()->json(['status' => 'success']);
    }

    public function passbook()
    {
        $passbook = WalletPassbook::where('user_id', Auth::id())->latest()->get();
        return response()->json($passbook);
    }

    /**
     * Transférer des CFA du wallet_balance vers le eco_token_balance
     */
    public function transferToEco(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:100', // Amount in CFA
        ]);

        try {
            $user = Auth::user();
            $amountCfa = $request->amount;
            $newWallet = 0;
            $newEco = 0;
            $ecoAmount = 0;

            \DB::transaction(function () use ($user, $amountCfa, &$ecoAmount, &$newWallet, &$newEco) {
                // Verrouiller la ligne utilisateur (Anti Double-Dépense)
                $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();

                if ($lockedUser->wallet_balance < $amountCfa) {
                    throw new Exception('Solde CFA insuffisant.');
                }

                $lockedUser->wallet_balance -= $amountCfa;
                $ecoAmount = $amountCfa / 1000.0;
                $lockedUser->eco_token_balance += $ecoAmount;
                $lockedUser->save();

                WalletPassbook::create([
                    'user_id' => $lockedUser->id,
                    'amount' => -$amountCfa,
                    'status' => 'DEBITED',
                    'via' => 'ECO_TRANSFER',
                    'description' => 'Achat de ' . $ecoAmount . ' ECO',
                ]);

                $newWallet = $lockedUser->wallet_balance;
                $newEco = $lockedUser->eco_token_balance;
            });

            return response()->json([
                'success' => true,
                'message' => 'Vous avez acheté ' . $ecoAmount . ' ECO.',
                'wallet_balance' => $newWallet,
                'eco_token_balance' => $newEco
            ]);
        } catch (Exception $e) {
            Log::error("Erreur transfert ECO : " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Webhook IPN CinetPay — Recharge portefeuille Utilisateur
     * POST /api/wallet/webhook/cinetpay
     * (Déclenché automatiquement par CinetPay après confirmation du paiement)
     */
    public function cinetpayWebhook(Request $request)
    {
        // CinetPay envoie cpm_trans_id ou transaction_id dans l'IPN
        $transactionId = $request->input('cpm_trans_id')
            ?: $request->input('transaction_id')
            ?: null;

        if (!$transactionId) {
            Log::warning('CinetPay Webhook : aucun transaction_id reçu.');
            return response()->json(['message' => 'No transaction_id'], 400);
        }

        try {
            \DB::transaction(function () use ($transactionId, $request) {
                $passbook = WalletPassbook::where('transaction_desc', $transactionId)
                    ->where('status', 'PENDING')
                    ->where('via', 'CINETPAY')
                    ->lockForUpdate()
                    ->first();

                if (!$passbook) {
                    // Déjà traité ou introuvable
                    return;
                }

                // Vérifier le statut auprès de CinetPay (optionnel si confiance totale en IPN)
                $cpmPaydoMsg = $request->input('cpm_paydoMsg') ?: $request->input('payment_status');
                if ($cpmPaydoMsg && strtolower($cpmPaydoMsg) !== 'success') {
                    $passbook->status = 'FAILED';
                    $passbook->save();
                    return;
                }

                $passbook->status = 'CREDITED';
                $passbook->save();

                $lockedUser = User::where('id', $passbook->user_id)->lockForUpdate()->first();
                if ($lockedUser) {
                    $lockedUser->wallet_balance += $passbook->amount;
                    $lockedUser->save();

                    Log::info("CinetPay IPN : wallet crédité de {$passbook->amount} CFA pour user {$lockedUser->id}");
                }
            });

            return response()->json(['message' => 'OK']);
        } catch (Exception $e) {
            Log::error('CinetPay Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
