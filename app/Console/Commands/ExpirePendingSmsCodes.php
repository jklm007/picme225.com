<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OfflineBookingSms;
use App\Models\UserRequests;
use App\Models\SmsOutbox;
use Illuminate\Support\Facades\Log;

/**
 * Commande de sécurité : expire tous les OfflineBookingSms dont expires_at est dépassé
 * et dont le statut est toujours PENDING.
 *
 * Complète le Job ExpireOfflineBookingSms (qui est dispatché par booking individuel)
 * en cas de panne de queue ou de restart du worker.
 *
 * Planifiée toutes les minutes dans le Kernel.
 */
class ExpirePendingSmsCodes extends Command
{
    protected $signature   = 'sms:expire-pending';
    protected $description = 'Expire les codes SMS de booking offline qui ont dépassé leur délai sans réponse.';

    public function handle()
    {
        $expired = OfflineBookingSms::where('status', 'PENDING')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expired->isEmpty()) {
            return 0;
        }

        Log::info("[ExpirePendingSmsCodes] {$expired->count()} code(s) SMS expiré(s) à traiter.");

        foreach ($expired as $booking) {
            $booking->status = 'EXPIRED';
            $booking->save();

            Log::info("[ExpirePendingSmsCodes] Code #{$booking->id} (sms_code={$booking->sms_code}) → EXPIRED.");

            // Y a-t-il encore d'autres codes PENDING pour cette même course ?
            $hasOtherPending = OfflineBookingSms::where('request_id', $booking->request_id)
                ->where('id', '!=', $booking->id)
                ->where('status', 'PENDING')
                ->exists();

            if ($hasOtherPending) {
                continue; // D'autres chauffeurs sont encore contactés
            }

            // Plus aucun chauffeur disponible : annuler la course
            $request = UserRequests::find($booking->request_id);
            if (!$request || $request->status !== 'SEARCHING') {
                continue;
            }

            Log::info("[ExpirePendingSmsCodes] Course #{$request->id} : tous les codes expirés → CANCELLED.");

            $request->status = 'CANCELLED';
            $request->save();

            // Supprimer les filtres restants
            \App\Models\RequestFilter::where('request_id', $request->id)->delete();

            // Push notification (best-effort)
            try {
                (new \App\Http\Controllers\SendPushNotification)->ProviderNotAvailable($request->user_id);
            } catch (\Exception $e) {
                Log::error("[ExpirePendingSmsCodes] Push error : " . $e->getMessage());
            }

            // SMS au client si son numéro est connu
            if ($request->user && !empty($request->user->mobile)) {
                $dest    = $request->d_address ?? 'votre course';
                $message = "[PicME] Désolé, aucun chauffeur n'est disponible pour {$dest} pour le moment. Veuillez réessayer dans quelques minutes.";

                SmsOutbox::create([
                    'phone_number'    => $request->user->mobile,
                    'message'         => $message,
                    'status'          => 'PENDING',
                    'gateway_node_id' => null,
                ]);

                Log::info("[ExpirePendingSmsCodes] SMS annulation envoyé au client ({$request->user->mobile}) pour la course #{$request->id}.");
            }
        }

        $this->info("[sms:expire-pending] {$expired->count()} code(s) expiré(s) traité(s).");
        return 0;
    }
}
