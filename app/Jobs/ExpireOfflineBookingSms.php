<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\OfflineBookingSms;
use App\Models\UserRequests;
use Illuminate\Support\Facades\Log;

class ExpireOfflineBookingSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $offlineBookingId;

    /**
     * Create a new job instance.
     *
     * @param int $offlineBookingId
     * @return void
     */
    public function __construct($offlineBookingId)
    {
        $this->offlineBookingId = $offlineBookingId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $booking = OfflineBookingSms::find($this->offlineBookingId);

        if (!$booking) {
            return;
        }

        if ($booking->status === 'PENDING') {
            Log::info("[ExpireOfflineBookingSms] Expiration de la demande SMS #{$booking->id} (Code: {$booking->sms_code}) pour le chauffeur #{$booking->provider_id}.");
            
            $booking->status = 'EXPIRED';
            $booking->save();

            // Vérifier si le trajet sous-jacent est toujours en recherche (SEARCHING)
            $request = UserRequests::find($booking->request_id);
            if ($request && $request->status === 'SEARCHING') {
                // Vérifier s'il reste d'autres demandes SMS en attente pour cette même course
                $hasOtherPending = OfflineBookingSms::where('request_id', $request->id)
                    ->where('id', '!=', $booking->id)
                    ->where('status', 'PENDING')
                    ->exists();

                if (!$hasOtherPending) {
                    Log::info("[ExpireOfflineBookingSms] Toutes les demandes SMS pour la course #{$request->id} ont expiré. Annulation de la course.");
                    
                    $request->status = 'CANCELLED';
                    $request->save();

                    // Supprimer les filtres de requêtes restants
                    \App\Models\RequestFilter::where('request_id', $request->id)->delete();

                    // Push notification (best-effort)
                    try {
                        (new \App\Http\Controllers\SendPushNotification)->ProviderNotAvailable($request->user_id);
                    } catch (\Exception $e) {
                        Log::error("[ExpireOfflineBookingSms] Erreur lors de la notification de non-disponibilité : " . $e->getMessage());
                    }

                    // SMS au client si son numéro est connu
                    if ($request->user && !empty($request->user->mobile)) {
                        $dest    = $request->d_address ?? 'votre course';
                        $message = "[PicME] Désolé, aucun chauffeur n'est disponible pour {$dest} pour le moment. Veuillez réessayer dans quelques minutes.";
                        \App\Models\SmsOutbox::create([
                            'phone_number'    => $request->user->mobile,
                            'message'         => $message,
                            'status'          => 'PENDING',
                            'gateway_node_id' => null,
                        ]);
                        Log::info("[ExpireOfflineBookingSms] SMS annulation → client ({$request->user->mobile}), course #{$request->id}.");
                    }
                }
            }
        }
    }
}
