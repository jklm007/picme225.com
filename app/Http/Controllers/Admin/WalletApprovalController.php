<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletPassbook;
use App\Models\ProviderWallet;
use App\Models\User;
use App\Models\Provider;
use Illuminate\Http\Request;
use DB;
use Exception;

/**
 * AdminWalletApprovalController
 * ─────────────────────────────
 * Permet à l'admin de valider ou rejeter les recharges manuelles
 * (PENDING) créées en mode Soft Launch (PAYMENT_GATEWAY=MANUAL).
 *
 * CONFORMITÉ UEMOA : seul l'administrateur peut créditer le wallet
 * après vérification manuelle de la preuve de paiement.
 */
class WalletApprovalController extends Controller
{
    /**
     * Lister toutes les transactions PENDING (users + providers)
     * GET /admin/wallet-approvals
     */
    public function index()
    {
        // Transactions PENDING des utilisateurs (clients)
        $userPending = WalletPassbook::where('status', 'PENDING')
            ->with('user')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($item) => array_merge($item->toArray(), ['account_type' => 'USER']));

        // Transactions PENDING des prestataires (providers)
        $providerPending = ProviderWallet::where('type', 'PENDING')
            ->with('provider')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($item) => array_merge($item->toArray(), ['account_type' => 'PROVIDER']));

        $pending = $userPending->merge($providerPending)->sortByDesc('created_at')->values();

        return view('admin.wallet.approvals', [
            'pending' => $pending,
            'total'   => $pending->count(),
        ]);
    }

    /**
     * Approuver une recharge PENDING (User)
     * POST /admin/wallet-approvals/user/{id}/approve
     */
    public function approveUser(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $passbook = WalletPassbook::where('id', $id)
                    ->where('status', 'PENDING')
                    ->lockForUpdate()
                    ->firstOrFail();

                // Créditer le wallet de l'utilisateur
                $user = User::where('id', $passbook->user_id)->lockForUpdate()->first();
                if (!$user) throw new Exception('Utilisateur introuvable.');

                $user->wallet_balance += $passbook->amount;
                $user->save();

                // Mettre à jour le statut de la transaction
                $passbook->status = 'CREDITED';
                $passbook->save();
            });

            return redirect()->route('admin.wallet-approvals.index')
                ->with('flash_success', 'Recharge de ' . WalletPassbook::find($id)->amount . ' CFA approuvée.');
        } catch (Exception $e) {
            return redirect()->back()->with('flash_error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Rejeter une recharge PENDING (User)
     * POST /admin/wallet-approvals/user/{id}/reject
     */
    public function rejectUser(Request $request, $id)
    {
        try {
            $passbook = WalletPassbook::where('id', $id)
                ->where('status', 'PENDING')
                ->firstOrFail();

            $passbook->status = 'REJECTED';
            $passbook->save();

            return redirect()->route('admin.wallet-approvals.index')
                ->with('flash_success', 'Recharge rejetée.');
        } catch (Exception $e) {
            return redirect()->back()->with('flash_error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Approuver une recharge PENDING (Provider)
     * POST /admin/wallet-approvals/provider/{id}/approve
     */
    public function approveProvider(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $wallet = ProviderWallet::where('id', $id)
                    ->where('type', 'PENDING')
                    ->lockForUpdate()
                    ->firstOrFail();

                $provider = Provider::where('id', $wallet->provider_id)->lockForUpdate()->first();
                if (!$provider) throw new Exception('Prestataire introuvable.');

                $provider->wallet_balance += $wallet->amount;
                $provider->save();

                $wallet->type    = 'CREDIT';
                $wallet->balance = $provider->wallet_balance;
                $wallet->transaction_desc = str_replace('en attente de validation', 'validée par admin', $wallet->transaction_desc);
                $wallet->save();
            });

            return redirect()->route('admin.wallet-approvals.index')
                ->with('flash_success', 'Recharge prestataire approuvée.');
        } catch (Exception $e) {
            return redirect()->back()->with('flash_error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Rejeter une recharge PENDING (Provider)
     * POST /admin/wallet-approvals/provider/{id}/reject
     */
    public function rejectProvider(Request $request, $id)
    {
        try {
            $wallet = ProviderWallet::where('id', $id)
                ->where('type', 'PENDING')
                ->firstOrFail();

            $wallet->type             = 'REJECTED';
            $wallet->transaction_desc = '[REJETÉ] ' . $wallet->transaction_desc;
            $wallet->save();

            return redirect()->route('admin.wallet-approvals.index')
                ->with('flash_success', 'Recharge rejetée.');
        } catch (Exception $e) {
            return redirect()->back()->with('flash_error', 'Erreur : ' . $e->getMessage());
        }
    }
}
