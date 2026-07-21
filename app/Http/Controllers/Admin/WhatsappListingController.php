<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MarketplaceListing;
use App\Models\WhatsappMessage;
use App\Models\WhatsappUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappListingController extends Controller
{
    /**
     * Display a listing of the whatsapp generated ads.
     */
    public function index(Request $request)
    {
        try {
            $status = $request->get('status', 'PENDING_VALIDATION');
            
            $listings = MarketplaceListing::with(['whatsappMessage', 'whatsappMessage.sender'])
                        ->where('source', 'whatsapp');
                        
            if ($status !== 'ALL') {
                $listings->where('status', $status);
            }

            $listings = $listings->orderBy('created_at', 'desc')->paginate(15);
            
            // KPIs
            $stats = [
                'total' => MarketplaceListing::where('source', 'whatsapp')->count(),
                'pending' => MarketplaceListing::where('source', 'whatsapp')->where('status', 'PENDING_VALIDATION')->count(),
                'active' => MarketplaceListing::where('source', 'whatsapp')->where('status', 'ACTIVE')->count(),
                'rejected' => MarketplaceListing::where('source', 'whatsapp')->where('status', 'REJECTED')->count(),
                'blacklisted' => WhatsappUser::where('is_blacklisted', true)->count(),
            ];
            
            $view = view('admin.whatsapp.index', compact('listings', 'status', 'stats'))->render();
            return response($view);
        } catch (\Exception $e) {
            \Log::error('WhatsappListingController@index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->with('flash_error', 'Une erreur est survenue. Veuillez réessayer.');
        }
    }

    /**
     * Approve a listing.
     */
    public function approve($id)
    {
        $listing = MarketplaceListing::findOrFail($id);
        $listing->status = 'ACTIVE'; 
        $listing->save();

        // Notify the ad author via WhatsApp
        if (!empty($listing->owner_phone)) {
            $this->notifyAuthor(
                $listing->owner_phone,
                "✅ *Votre annonce a été validée*\n\nFélicitations ! Votre annonce a été validée et est maintenant visible par tous les utilisateurs sur PickMe225.\n\n🔗 Voir mon annonce : " . url('/marketplace/' . $listing->id) . "\n🔄 Partager mon annonce : " . url('/marketplace/' . $listing->id)
            );
        }

        return back()->with('flash_success', 'Annonce validée et publiée avec succès !');
    }

    /**
     * Reject a listing.
     */
    public function reject($id)
    {
        $listing = MarketplaceListing::findOrFail($id);
        $listing->status = 'REJECTED';
        $listing->save();

        // Notify the ad author via WhatsApp
        if (!empty($listing->owner_phone)) {
            $this->notifyAuthor(
                $listing->owner_phone,
                "❌ Votre annonce *{$listing->title}* n'a pas pu être validée sur PicMe225. Si vous pensez que c'est une erreur, vous pouvez republier votre annonce avec des photos plus claires et un prix indiqué."
            );
        }

        return back()->with('flash_success', 'Annonce rejetée.');
    }

    /**
     * Delete a listing.
     */
    public function destroy($id)
    {
        $listing = MarketplaceListing::findOrFail($id);
        
        // Optionnel : supprimer aussi le message WhatsApp ?
        // $listing->whatsappMessage()->delete();
        
        $listing->delete();

        return back()->with('flash_success', 'Annonce supprimée définitivement.');
    }

    /**
     * Bulk action (approve, reject, delete)
     */
    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('selected_ids', []);

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('flash_error', 'Aucune annonce sélectionnée.');
        }

        $count = count($ids);

        if ($action === 'approve') {
            MarketplaceListing::whereIn('id', $ids)->update(['status' => 'ACTIVE']);
            return redirect()->back()->with('flash_success', $count . ' annonce(s) approuvée(s) avec succès.');
        } 
        elseif ($action === 'reject') {
            MarketplaceListing::whereIn('id', $ids)->update(['status' => 'REJECTED']);
            return redirect()->back()->with('flash_success', $count . ' annonce(s) rejetée(s) avec succès.');
        }
        elseif ($action === 'delete') {
            MarketplaceListing::whereIn('id', $ids)->delete();
            return redirect()->back()->with('flash_success', $count . ' annonce(s) supprimée(s) avec succès.');
        }

        return redirect()->back()->with('flash_error', 'Action invalide.');
    }

    /**
     * Toggle Blacklist status for a user.
     */
    public function blacklistUser($phone)
    {
        $user = WhatsappUser::where('phone_number', $phone)->first();
        if ($user) {
            $user->is_blacklisted = !$user->is_blacklisted;
            $user->save();
            $msg = $user->is_blacklisted ? "Utilisateur {$phone} ajouté à la blacklist." : "Utilisateur {$phone} retiré de la blacklist.";
            return back()->with('flash_success', $msg);
        }
        return back()->with('flash_error', 'Utilisateur introuvable.');
    }

    private function notifyAuthor(string $phone, string $message): void
    {
        $evoUrl      = config('services.evolution.url')  ?: env('EVOLUTION_API_URL',  'http://evolution-api-service:8080');
        $evoKey      = config('services.evolution.key')  ?: env('EVOLUTION_API_KEY',  'picme225-evolution-secret-key');
        $evoInstance = config('services.evolution.instance') ?: env('EVOLUTION_INSTANCE', 'picme_whatsapp');

        if (empty($evoUrl) || empty($evoKey)) {
            Log::warning('WhatsappListingController: Evolution API not configured, notification skipped.');
            return;
        }

        // Always use phone_number@s.whatsapp.net for private messages — this format works reliably.
        // The whatsapp_id (LID) stored in DB causes ERROR on private sends.
        $whatsappUser = \App\Models\WhatsappUser::where('phone_number', $phone)->first();
        $rawPhone = preg_replace('/[^0-9]/', '', $whatsappUser ? $whatsappUser->phone_number : $phone);
        $whatsappId = $rawPhone . '@s.whatsapp.net';

        try {
            $resp = Http::withHeaders(['apikey' => $evoKey])
                ->timeout(10)
                ->post("{$evoUrl}/message/sendText/{$evoInstance}", [
                    'number'  => $whatsappId,
                    'text'    => $message,
                    'options' => [
                        'delay'    => 1200,
                        'presence' => 'composing',
                    ],
                ]);

            Log::info('notifyAuthor (WhatsappListing) envoyé', [
                'phone'  => $phone,
                'to'     => $whatsappId,
                'status' => $resp->status(),
            ]);

            if ($resp->failed()) {
                Log::warning('notifyAuthor (WhatsappListing) failed', [
                    'status' => $resp->status(),
                    'body'   => $resp->body(),
                    'phone'  => $phone,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('notifyAuthor (WhatsappListing) exception: ' . $e->getMessage());
        }
    }
}

