<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TransportEvent;
use App\Models\TransportTicket;
use App\Models\EventPassType;

class TicketController extends Controller
{
    public function index()
    {
        // Filtrer les annonces de la catégorie TICKETS
        $events = MarketplaceListing::where('category', 'TICKETS')
            ->with('user', 'passes')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('admin.tickets.index', compact('events'));
    }

    public function passes()
    {
        $passes = EventPassType::with('listing')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.tickets.passes', compact('passes'));
    }

    public function sold()
    {
        $tickets = TransportTicket::with('listing', 'pass', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        $stats = [
            'total_revenue' => TransportTicket::where('payment_status', 'PAID')->sum('total_price'),
            'cash_count' => TransportTicket::where('payment_mode', 'CASH')->count(),
            'wallet_count' => TransportTicket::where('payment_mode', 'WALLET')->count(),
        ];

        return view('admin.tickets.sold', compact('tickets', 'stats'));
    }

    public function createSell()
    {
        $events = \App\Models\MarketplaceListing::where('category', 'TICKETS')
            ->whereIn('status', ['ACTIVE', 'APPROVED'])
            ->orderBy('title', 'asc')
            ->get();
            
        return view('admin.tickets.sell', compact('events'));
    }

    public function apiPasses($id)
    {
        $passes = EventPassType::where('listing_id', $id)->get();
        return response()->json($passes);
    }

    public function storeSell(Request $request)
    {
        $request->validate([
            'listing_id' => 'required|exists:marketplace_listings,id',
            'pass_type_id' => 'nullable',
            'phone' => 'required|string',
            'payment_mode' => 'required|in:CASH,ADMIN_CASH'
        ]);

        try {
            $phone = $request->input('phone');
            // Formater si besoin (ex: +225)
            if (!str_starts_with($phone, '+') && strlen($phone) == 10) {
                $phone = '+225' . $phone;
            }

            // Recherche de l'utilisateur
            $user = \App\Models\User::where('mobile', $phone)
                ->orWhere('mobile', str_replace('+225', '', $phone))
                ->first();

            if (!$user) {
                $user = \App\Models\User::create([
                    'first_name' => $request->input('first_name') ?: 'Invité',
                    'last_name' => $request->input('last_name') ?: '',
                    'email' => $request->input('email') ?: ('guest_' . time() . '@picme225.com'),
                    'mobile' => $phone,
                    'password' => bcrypt(\Illuminate\Support\Str::random(12)),
                    'payment_mode' => 'CASH',
                    'device_type' => 'android',
                    'login_by' => 'manual'
                ]);
            }

            $service = new \App\Services\TicketPurchaseService();
            // L'admin passe la position par défaut
            $service->purchase(
                $request->listing_id, 
                $user, 
                $request->payment_mode, 
                $request->pass_type_id, 
                5.3484, 
                -4.0244
            );

            return redirect()->route('admin.tickets.sold')->with('flash_success', 'Billet généré et envoyé par WhatsApp avec succès !');

        } catch (\Exception $e) {
            \Log::error("Erreur Guichet Admin : " . $e->getMessage());
            return back()->with('flash_error', 'Erreur lors de la vente : ' . $e->getMessage())->withInput();
        }
    }

    public function resendTicket($id)
    {
        try {
            $ticket = \App\Models\TransportTicket::findOrFail($id);
            
            if ($ticket->user) {
                if (class_exists(\App\Jobs\SendWhatsAppTicketJob::class)) {
                    dispatch(new \App\Jobs\SendWhatsAppTicketJob($ticket, $ticket->user));
                }
                return back()->with('flash_success', 'Le ticket a été renvoyé par WhatsApp avec succès !');
            }
            
            return back()->with('flash_error', "Impossible de renvoyer : Aucun utilisateur lié à ce ticket.");
        } catch (\Exception $e) {
            \Log::error("Erreur lors du renvoi de ticket : " . $e->getMessage());
            return back()->with('flash_error', 'Erreur lors du renvoi du ticket : ' . $e->getMessage());
        }
    }
}
