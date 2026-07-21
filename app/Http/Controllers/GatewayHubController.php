<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GatewayNode;
use App\Models\OfflineBookingSms;
use App\Models\SmsOutbox;
use Exception;

class GatewayHubController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display the gateway management hub.
     */
    public function index()
    {
        try {
            $nodes = \App\Models\GatewayNode::orderBy('id', 'desc')->get();
            
            // Calculs pour le résumé de rentabilité (uniquement les recharges Gateway P2P)
            $total_p2p_recharges = \App\Models\WalletPassbook::where('status', 'CREDITED')
                                        ->where('via', 'MOBILE_MONEY')
                                        ->sum('amount');
            $total_savings = $total_p2p_recharges * 0.035; 
            
            // Bénéfices nets (commissions des profits du Node PROFIT)
            $profitNode = \App\Models\GatewayNode::where('type', 'PROFIT')->first();
            $total_commissions = $profitNode ? $profitNode->current_balance : 0;

            // Stats SMS Booking offline
            $sms_pending  = OfflineBookingSms::where('status', 'PENDING')->count();
            $sms_accepted = OfflineBookingSms::where('status', 'ACCEPTED')
                ->where('updated_at', '>=', now()->subHours(24))->count();
            $sms_expired  = OfflineBookingSms::where('status', 'EXPIRED')
                ->where('updated_at', '>=', now()->subHours(24))->count();

            // Dernières courses offline (50)
            $offline_bookings = OfflineBookingSms::with(['userRequest', 'provider'])
                ->orderBy('id', 'desc')->take(50)->get();

            // Derniers SMS sortants (30)
            $sms_outbox = SmsOutbox::orderBy('id', 'desc')->take(30)->get();
            
            return view('admin.gateway.index', compact(
                'nodes', 
                'total_p2p_recharges', 
                'total_savings', 
                'total_commissions',
                'sms_pending',
                'sms_accepted',
                'sms_expired',
                'offline_bookings',
                'sms_outbox'
            ));
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors du chargement du Hub Gateway');
        }
    }

    /**
     * Store a new gateway node.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'phone_number' => 'required',
            'network' => 'required',
            'type' => 'required|in:RECEIVER,PAYOUT,VAULT,PROFIT',
        ]);

        try {
            \App\Models\GatewayNode::create($request->all());
            return back()->with('flash_success', 'Nouvelle Gateway (SIM) configurée avec succès');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Toggle the status of a gateway node.
     */
    public function toggleStatus($id)
    {
        try {
            $node = GatewayNode::findOrFail($id);
            $node->status = ($node->status == 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
            $node->save();
            return back()->with('flash_success', 'Statut de la Gateway mis à jour');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la mise à jour du statut');
        }
    }
}
