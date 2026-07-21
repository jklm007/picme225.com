<?php

namespace App\Http\Controllers\ProviderResources;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\Models\ProviderWallet;
use App\Models\Provider;
use Exception;

class WalletController extends Controller
{
    /**
     * Get provider wallet balance and transactions.
     */
    public function index()
    {
        try {
            $provider = Auth::user();
            $wallet_balance = $provider->wallet_balance;
            $eco_wallet_balance = $provider->eco_wallet_balance;

            $transactions = ProviderWallet::where('provider_id', $provider->id)
                ->orderBy('created_at', 'desc')
                ->take(50)
                ->get();

            return response()->json([
                'wallet_balance' => (float) $wallet_balance,
                'eco_wallet_balance' => (float) $eco_wallet_balance,
                'wallet_transactions' => $transactions
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Recharge réelle via CinetPay (Orange, MTN, Moov, Wave, Card).
     */
    public function recharge(Request $request)
    {
        $this->validate($request, [
            'amount'       => 'required|numeric|min:100',
            'payment_mode' => 'nullable|string',
        ]);

        try {
            $provider = Auth::user();
            $amount   = $request->amount;

            // Lecture du Feature Flag (compatible avec le Dashboard Admin)
            $gateway = \Setting::get('payment_gateway', config('services.payment_gateway', 'MANUAL'));

            if ($gateway !== 'CINETPAY' || !config('mobile_money.providers.cinetpay.api_key')) {
                // Mode MANUEL : On retourne le numéro du Node actif.
                // Le robot SMS (Android) détecte le paiement et crédite automatiquement.
                return $this->getManualReceiverInfo($provider, $amount, $request->payment_mode);
            }

            $mmService = new \App\Services\MobileMoneyService('cinetpay');
            $reference = 'RECH_' . time() . '_' . $provider->id;

            // Création de la transaction locale
            \App\Models\MobileMoneyTransaction::create([
                'provider_id'    => $provider->id,
                'provider'       => 'cinetpay',
                'amount'         => $amount,
                'phone_number'   => $provider->mobile ?: '00000000',
                'transaction_id' => $reference,
                'reference'      => $reference,
                'type'           => 'WALLET_RECHARGE',
                'status'         => 'PENDING',
            ]);

            // Initiation CinetPay
            $result = $mmService->initiatePayment($amount, $provider->mobile, $reference);

            return response()->json([
                'message'        => 'Paiement initié',
                'payment_url'    => $result['payment_url'],
                'transaction_id' => $reference,
                'real'           => true
            ]);

        } catch (Exception $e) {
            \Log::error('Wallet Recharge Error: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'initiation du paiement: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mode Manuel — Retourne le numéro de réception actif pour que l'utilisateur envoie l'argent.
     * Le robot SMS (téléphone Android Gateway) crédite automatiquement dès réception du SMS.
     */
    private function getManualReceiverInfo($provider, $amount, $mode)
    {
        $network = strtoupper($mode ?: 'WAVE');

        // Trouver le Node actif pour ce réseau (RECEIVER en priorité)
        $node = \App\Models\GatewayNode::where('type', 'RECEIVER')
            ->where('network', $network)
            ->where('status', 'ACTIVE')
            ->whereRaw('daily_volume < daily_limit')
            ->orderBy('daily_volume', 'asc')
            ->first();

        // Fallback sur un Node PAYOUT si RECEIVER plein
        if (!$node) {
            $node = \App\Models\GatewayNode::where('network', $network)
                ->where('status', 'ACTIVE')
                ->whereRaw('daily_volume < daily_limit')
                ->orderBy('daily_volume', 'asc')
                ->first();
        }

        if (!$node) {
            return response()->json([
                'error' => 'Aucun point de réception disponible pour ' . $network . ' en ce moment. Veuillez réessayer ou choisir un autre réseau.'
            ], 503);
        }

        return response()->json([
            'message'       => 'Envoyez exactement ' . $amount . ' CFA au numéro ci-dessous. Votre compte sera rechargé automatiquement après confirmation.',
            'receiver_name' => $node->name,
            'receiver_phone'=> $node->phone_number,
            'network'       => $network,
            'amount'        => $amount,
            'instructions'  => 'Référence du paiement : PicMe-' . $provider->id . '-' . $amount,
            'real'          => false,
            'mode'          => 'MANUAL_GATEWAY',
        ]);
    }

    /**
     * Webhook de confirmation de paiement (IPN)
     */
    public function callback(Request $request)
    {
        // CinetPay envoie cpm_trans_id ou transaction_id
        $reference = $request->cpm_trans_id ?: $request->transaction_id;

        if (!$reference) {
            return response()->json(['message' => 'No reference found'], 400);
        }

        try {
            $transaction = \App\Models\MobileMoneyTransaction::where('transaction_id', $reference)->firstOrFail();

            if ($transaction->status == 'SUCCESS') {
                return response()->json(['message' => 'Transaction already processed']);
            }

            $mmService = new \App\Services\MobileMoneyService('cinetpay');
            $status = $mmService->verifyTransaction($reference);

            if ($status == 'SUCCESS') {
                \DB::transaction(function () use ($transaction) {
                    $transaction->update([
                        'status' => 'SUCCESS',
                        'processed_at' => now(),
                        'provider_response' => request()->all()
                    ]);

                    $provider = Provider::find($transaction->provider_id);
                    if ($provider) {
                        $provider->wallet_balance += $transaction->amount;
                        $provider->save();

                        // Log Wallet
                        ProviderWallet::create([
                            'provider_id' => $provider->id,
                            'amount' => $transaction->amount,
                            'transaction_id' => $transaction->transaction_id,
                            'transaction_desc' => 'Recharge Réelle CinetPay CFA',
                            'type' => 'CREDIT',
                            'balance' => $provider->wallet_balance,
                        ]);
                    }
                });
                return response()->json(['message' => 'Wallet updated successfully']);
            } else {
                $transaction->update(['status' => 'FAILED', 'provider_response' => request()->all()]);
                return response()->json(['message' => 'Payment failed']);
            }

        } catch (Exception $e) {
            \Log::error('Payment Callback Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Page de retour après paiement
     */
    public function status(Request $request)
    {
        $ref = $request->ref;
        return view('wallet_status', ['reference' => $ref]);
    }

    /**
     * Transfer CFA from wallet_balance to eco_wallet_balance
     */
    public function rewardAdMob(Request $request)
    {
        try {
            $provider = Auth::user();
            if (!$provider) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }

            // Reward amount for watching ad
            $rewardAmount = 0.1;

            $provider->eco_wallet_balance += $rewardAmount;
            $provider->save();

            // Enregistrer la transaction
            WalletPassbook::create([
                'provider_id' => $provider->id,
                'amount' => $rewardAmount,
                'status' => 'CREDITED',
                'via' => 'AdMob Reward Video',
            ]);

            return response()->json([
                'message' => 'Félicitations ! Vous avez gagné '.$rewardAmount.' ECO.',
                'eco_wallet_balance' => $provider->eco_wallet_balance
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function transferToEco(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:100', // Amount in CFA
        ]);

        try {
            $provider    = Auth::user();
            $amountCfa   = $request->amount;
            $ecoAmount   = 0;

            \DB::transaction(function () use ($provider, $amountCfa, &$ecoAmount) {
                // Verrou pessimiste — prévient la race condition (double dépense)
                $lockedProvider = Provider::where('id', $provider->id)->lockForUpdate()->first();

                if ($lockedProvider->wallet_balance < $amountCfa) {
                    throw new Exception('Solde CFA insuffisant pour ce transfert.');
                }

                $lockedProvider->wallet_balance  -= $amountCfa;
                $ecoAmount = $amountCfa / 1000.0;
                $lockedProvider->eco_wallet_balance += $ecoAmount;
                $lockedProvider->save();

                // Log the debit from CFA Wallet
                ProviderWallet::create([
                    'provider_id'      => $lockedProvider->id,
                    'amount'           => -$amountCfa,
                    'transaction_id'   => 'TRSF_' . time() . '_' . rand(100, 999),
                    'transaction_desc' => 'Transfert vers solde ECO (-' . $amountCfa . ' CFA / +' . $ecoAmount . ' ECO)',
                    'type'             => 'DEBIT',
                    'balance'          => $lockedProvider->wallet_balance,
                ]);
            });

            return response()->json([
                'message'           => 'Transfert réussi. Vous avez reçu ' . $ecoAmount . ' ECO.',
                'wallet_balance'    => (float) $provider->fresh()->wallet_balance,
                'eco_wallet_balance'=> (float) $provider->fresh()->eco_wallet_balance
            ]);
        } catch (Exception $e) {
            \Log::error('Transfer to ECO Error: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du transfert: ' . $e->getMessage()], 500);
        }
    }
}
