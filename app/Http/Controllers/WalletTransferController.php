<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Provider;
use App\Models\WalletPassbook;
use App\Models\ProviderWallet;
use App\Http\Controllers\SendPushNotification;
use Auth;
use DB;
use Exception;

class WalletTransferController extends Controller
{
    const TRANSACTION_FEE_PERCENT = 1; // 1% pour transfert interne
    const WITHDRAW_FEE_PERCENT = 2;    // 2% pour retrait externe

    /**
     * Génère un QR Code dynamique, crypté en AES-256 et limité dans le temps.
     */
    public function generateQrToken(Request $request)
    {
        $user = Auth::user();
        $type = ($user instanceof Provider) ? 'PROVIDER' : 'USER';
        
        $payload = [
            'id' => $user->id,
            'type' => $type,
            'exp' => time() + (5 * 60), // Expire dans 5 minutes
            'nonce' => \Illuminate\Support\Str::random(64) // Anti-duplication
        ];
        
        $encrypted = \Illuminate\Support\Facades\Crypt::encryptString(json_encode($payload));
        return response()->json(['qr_token' => 'PICME_ENC_' . $encrypted]);
    }

    /**
     * Résoudre un token QR en nom de destinataire (avant transfert).
     * GET /api/provider/wallet/lookup?qr_token=xxx
     */
    public function lookupByQr(Request $request)
    {
        $this->validate($request, ['qr_token' => 'required|string']);
        $qr_token = $request->qr_token;

        $recipient = null;

        if (str_starts_with($qr_token, 'PICME_ENC_')) {
            try {
                $encryptedStr = str_replace('PICME_ENC_', '', $qr_token);
                $payload = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($encryptedStr));
                
                if (isset($payload->exp) && $payload->exp < time()) {
                    return response()->json(['error' => 'Ce QR Code a expiré. Veuillez le rafraîchir.'], 400);
                }
                
                if (isset($payload->nonce) && \Illuminate\Support\Facades\Cache::has('qr_nonce_' . $payload->nonce)) {
                    return response()->json(['error' => 'Ce QR Code a déjà été utilisé.'], 400);
                }
                
                if (isset($payload->type) && $payload->type === 'PROVIDER') {
                    $recipient = Provider::find($payload->id);
                } else {
                    $recipient = User::find($payload->id);
                }
            } catch (\Exception $e) {
                return response()->json(['error' => 'QR Code invalide ou altéré.'], 400);
            }
        } else if (str_starts_with($qr_token, 'PICKME_START_')) {
            $id = str_replace('PICKME_START_', '', $qr_token);
            $recipient = Provider::find($id);
        } else {
            $recipient = User::where('qr_token', $qr_token)->first();
            if (!$recipient) {
                $recipient = Provider::where('qr_token', $qr_token)->first();
            }
            if (!$recipient && str_contains($qr_token, '_')) {
                $parts = explode('_', $qr_token, 2);
                if (count($parts) == 2) {
                    $id = $parts[0]; $email = $parts[1];
                    $recipient = User::where('id', $id)->where('email', $email)->first();
                    if (!$recipient) {
                        $recipient = Provider::where('id', $id)->where('email', $email)->first();
                    }
                }
            }
        }

        if (!$recipient) {
            return response()->json(['error' => 'Carte invalide ou destinataire introuvable.'], 404);
        }

        $sender = Auth::user();
        if ($sender->id == $recipient->id && get_class($sender) == get_class($recipient)) {
            return response()->json(['error' => 'Vous ne pouvez pas vous envoyer de l\'argent.'], 400);
        }

        return response()->json([
            'recipient_name'   => trim($recipient->first_name . ' ' . $recipient->last_name),
            'recipient_type'   => ($recipient instanceof Provider) ? 'Conducteur' : 'Passager',
            'recipient_avatar' => $recipient->avatar ?? null,
        ]);
    }

    /**
     * Transférer du CFA via Scan de Carte QR.
     * Supporte User-to-User, User-to-Provider, Provider-to-User, Provider-to-Provider.
     */
    public function transferByScan(Request $request)
    {
        $this->validate($request, [
            'qr_token' => 'required|string',
            'amount' => 'required|numeric|min:50',
            'notes' => 'nullable|string|max:100',
        ]);

        try {
            $sender = Auth::user();
            $amount = $request->amount;
            $qr_token = $request->qr_token;

            // 1. Identifier le destinataire
            $recipientType = 'USER';
            $recipient = null;

            if (str_starts_with($qr_token, 'PICME_ENC_')) {
                try {
                    $encryptedStr = str_replace('PICME_ENC_', '', $qr_token);
                    $payload = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($encryptedStr));
                    
                    if (isset($payload->exp) && $payload->exp < time()) {
                        return response()->json(['error' => 'Ce QR Code a expiré. Veuillez le rafraîchir.'], 400);
                    }
                    
                    if (isset($payload->nonce)) {
                        if (\Illuminate\Support\Facades\Cache::has('qr_nonce_' . $payload->nonce)) {
                            return response()->json(['error' => 'Ce QR Code a déjà été utilisé.'], 400);
                        }
                        // Marquer le nonce comme utilisé
                        \Illuminate\Support\Facades\Cache::put('qr_nonce_' . $payload->nonce, true, 300); // 5 minutes
                    }
                    
                    if (isset($payload->type) && $payload->type === 'PROVIDER') {
                        $recipient = Provider::find($payload->id);
                        $recipientType = 'PROVIDER';
                    } else {
                        $recipient = User::find($payload->id);
                        $recipientType = 'USER';
                    }
                } catch (\Exception $e) {
                    return response()->json(['error' => 'QR Code crypté invalide ou corrompu.'], 400);
                }
            } else if (str_starts_with($qr_token, 'PICKME_START_')) {
                // ══════════════════════════════════════════════════════════
                // FORMAT LEGACY REJETÉ — QR Code statique sans expiration.
                // Risque sécurité : un QR volé peut être utilisé indéfiniment.
                // L'utilisateur doit régénérer sa carte QR depuis l'application.
                // ══════════════════════════════════════════════════════════
                return response()->json([
                    'error' => 'Format QR obsolète. Veuillez rafraîchir votre carte PicMe dans l\'application pour obtenir un QR Code sécurisé.'
                ], 400);
            } else {
                // ══════════════════════════════════════════════════════════
                // FORMAT EMAIL/ID REJETÉ — QR Code statique sans expiration.
                // ══════════════════════════════════════════════════════════
                return response()->json([
                    'error' => 'Format QR non reconnu ou obsolète. Veuillez utiliser votre carte PicMe mise à jour.'
                ], 400);
            }

            if (!$recipient) {
                return response()->json(['error' => 'Destinataire introuvable ou carte invalide.'], 404);
            }

            // Empêcher l'auto-transfert
            if ($sender->id == $recipient->id && get_class($sender) == get_class($recipient)) {
                return response()->json(['error' => 'Vous ne pouvez pas vous envoyer d\'argent à vous-même.'], 400);
            }

            // 2. Calculer les frais (1%)
            $commission = ($amount * self::TRANSACTION_FEE_PERCENT) / 100;
            $totalDeduction = $amount + $commission;

            // 3. Exécuter la transaction avec Verrouillage Pessimiste
            $newBalance = 0;
            DB::transaction(function () use ($sender, $recipient, $amount, $commission, $totalDeduction, $request, &$newBalance) {
                // Verrouiller les lignes pour empêcher la double dépense
                $lockedSender = ($sender instanceof Provider)
                    ? Provider::where('id', $sender->id)->lockForUpdate()->first()
                    : User::where('id', $sender->id)->lockForUpdate()->first();

                $lockedRecipient = ($recipient instanceof Provider)
                    ? Provider::where('id', $recipient->id)->lockForUpdate()->first()
                    : User::where('id', $recipient->id)->lockForUpdate()->first();

                // Re-calculer le solde sur la ligne verrouillée
                $senderBalance = $lockedSender->wallet_balance;

                // 4. Vérifier le solde de l'expéditeur (solde doit couvrir montant + frais)
                if ($senderBalance < $totalDeduction) {
                    throw new Exception('Solde CFA insuffisant. (Rappel : Les crédits ECO ne sont pas transférables à un tiers).');
                }

                // Débiter l'expéditeur (Montant + Frais)
                $lockedSender->wallet_balance -= $totalDeduction;
                $lockedSender->save();
                $newBalance = $lockedSender->wallet_balance;

                // Créditer le destinataire (Montant net)
                $lockedRecipient->wallet_balance += $amount;
                $lockedRecipient->save();

                $notes = $request->notes ?: 'Transfert via Carte PickMe';

                // Enregistrer l'historique pour l'expéditeur
                $this->logTransaction($lockedSender, -$totalDeduction, 'TRANSFER_SENT', $notes . " vers " . $lockedRecipient->qr_id . " (Frais: " . $commission . ")");

                // Enregistrer l'historique pour le destinataire
                $this->logTransaction($lockedRecipient, $amount, 'TRANSFER_RECEIVED', $notes . " de " . $lockedSender->qr_id);
                
                // Envoyer la notification Push aux deux parties (pour rafraîchissement automatique)
                $this->notifyRecipient($lockedRecipient, $amount, $lockedSender);
            });

            return response()->json([
                'message' => 'Transfert de ' . $amount . ' CFA réussi !',
                'fee' => $commission,
                'total_deducted' => $totalDeduction,
                'new_balance' => $newBalance,
                'recipient_name' => $recipient->first_name . ' ' . $recipient->last_name
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur technique: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Logique générique pour enregistrer dans les tables de passbook disparates
     */
    private function logTransaction($entity, $amount, $type, $desc)
    {
        if ($entity instanceof User) {
            WalletPassbook::create([
                'user_id' => $entity->id,
                'amount' => abs($amount),
                'status' => $amount > 0 ? 'CREDITED' : 'DEBITED',
                'via' => $type,
                'transaction_id' => 'TRSF-' . time() . '-' . $entity->id,
                'description' => $desc,
            ]);
        } else {
            ProviderWallet::create([
                'provider_id' => $entity->id,
                'amount' => abs($amount),
                'type' => $amount > 0 ? 'CREDIT' : 'DEBIT',
                'transaction_id' => 'TRSF-' . time() . '-' . $entity->id,
                'transaction_desc' => $desc,
                'balance' => $entity->wallet_balance,
            ]);
        }
    }

    /**
     * Envoyer une notification push aux DEUX parties (expéditeur + destinataire)
     * avec le type WALLET_TRANSFER pour déclencher le rafraîchissement automatique
     */
    private function notifyRecipient($recipient, $amount, $sender)
    {
        $notif = new SendPushNotification();

        // --- Notification au DESTINATAIRE (argent reçu) ---
        $recipientTitle = "💰 Argent reçu !";
        $recipientMsg   = "Vous avez reçu " . $amount . " CFA de " . $sender->first_name . " via PickMe Card.";
        $recipientData  = [
            'title'   => $recipientTitle,
            'message' => $recipientMsg,
            'type'    => 'WALLET_TRANSFER',
        ];
        if ($recipient instanceof User) {
            $notif->sendPushToUser($recipient->id, $recipientData, $recipientTitle);
        } else {
            $notif->sendPushToProvider($recipient->id, $recipientData);
        }

        // --- Notification à l'EXPÉDITEUR (confirmation de débit + refresh) ---
        $senderTitle = "✅ Transfert envoyé";
        $senderMsg   = $amount . " CFA envoyé à " . $recipient->first_name . " avec succès.";
        $senderData  = [
            'title'   => $senderTitle,
            'message' => $senderMsg,
            'type'    => 'WALLET_TRANSFER',
        ];
        if ($sender instanceof User) {
            $notif->sendPushToUser($sender->id, $senderData, $senderTitle);
        } else {
            $notif->sendPushToProvider($sender->id, $senderData);
        }
    }

    /**
     * Gérer la demande de retrait vers Mobile Money (Externe)
     */
    public function withdraw(Request $request)
    {
        // Allowed for both Users and Providers. Withdrawals are processed from wallet_balance (CFA).
        $sender = Auth::user();

        $this->validate($request, [
            'amount'       => 'required|numeric|min:100',
            'phone_number' => 'required',
            'network'      => 'required|string',
        ]);

        try {
            $sender = Auth::user();
            $amount = $request->amount;
            
            // 1. Calculer les frais de retrait (Minimum 100 CFA ou 2% pour les montants > 5000)
            $commission = max(100, ($amount * self::WITHDRAW_FEE_PERCENT) / 100);
            $totalDeduction = $amount + $commission;

            // 2. Traiter le retrait avec Verrouillage Pessimiste
            $withdrawalId = 'WDR-' . time() . '-' . $sender->id;
            $newBalance = 0;
            
            DB::transaction(function () use ($sender, $amount, $commission, $totalDeduction, $request, $withdrawalId, &$newBalance) {
                $lockedSender = ($sender instanceof \App\Models\Provider)
                    ? \App\Models\Provider::where('id', $sender->id)->lockForUpdate()->first()
                    : \App\Models\User::where('id', $sender->id)->lockForUpdate()->first();

                $senderBalance = $lockedSender->wallet_balance;

                if ($senderBalance < $totalDeduction) {
                    throw new Exception('Solde insuffisant pour ce retrait. (Rappel : Les crédits ECO ne sont pas convertibles ni retirables en Mobile Money).');
                }

                $lockedSender->wallet_balance -= $totalDeduction;
                $lockedSender->save();
                $newBalance = $lockedSender->wallet_balance;

                $desc = "Retrait vers " . $request->network . " (" . $request->phone_number . ") - Frais: " . $commission;
                
                // Loguer la transaction dans le passbook
                $this->logTransaction($sender, -$totalDeduction, 'WITHDRAW_REQUEST', $desc);
                
                // Enregistrer l'ordre dans la file d'attente du Robot (MobileMoneyTransaction)
                \App\Models\MobileMoneyTransaction::create([
                    'user_id' => ($sender instanceof \App\Models\User) ? $sender->id : null,
                    'provider_id' => ($sender instanceof \App\Models\Provider) ? $sender->id : null,
                    'provider' => $request->network,
                    'amount' => $amount,
                    'phone_number' => $request->phone_number,
                    'transaction_id' => $withdrawalId,
                    'type' => 'WITHDRAWAL',
                    'status' => 'PENDING'
                ]);

                // Envoyer l'ordre au Robot de Paiement (Gateway) via Push (en plus du polling)
                $notif = new SendPushNotification();
                $notif->sendPayoutRequestToGateway($amount, $request->phone_number, $request->network, $withdrawalId);
            });

            return response()->json([
                'message' => 'Votre demande de retrait de ' . $amount . ' CFA vers ' . $request->network . ' a été transmise au robot de paiement.',
                'new_balance' => $newBalance
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
