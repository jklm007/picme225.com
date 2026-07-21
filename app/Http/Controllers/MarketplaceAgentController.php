<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\MarketplaceListing;
use App\Models\RentalBooking;
use App\Models\UserRequests;
use App\Models\EventPassType;
use App\Models\TransportTicket;
use App\Models\WalletPassbook;
use App\Helpers\Helper;
use App\Models\ServiceType;
use App\Models\Post;
use App\Models\MarketplaceAgent;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MarketplaceAgentController extends Controller
{
        public function delegateAgent(Request $request, $id)
        {
            $listing = MarketplaceListing::findOrFail($id);
            if ($listing->user_id !== Auth::id()) return response()->json(['error' => 'Interdit'], 403);
    
            $request->validate(['mobile' => 'required']);
            $mobile = $request->mobile;
            
            $agentUser = \App\Models\User::where('mobile', $mobile)->first();
            $isGhost = false;
    
            if (!$agentUser) {
                // Création du Compte Fantôme (Ghost Account)
                $agentUser = \App\Models\User::create([
                    'first_name' => 'GHOST_AGENT',
                    'last_name' => 'PicMe',
                    'mobile' => $mobile,
                    'email' => null,
                    'password' => \Hash::make(\Illuminate\Support\Str::random(12)),
                    'device_type' => 'android',
                    'login_by' => 'manual',
                    'payment_mode' => 'CASH'
                ]);
                $isGhost = true;
            }
    
            MarketplaceAgent::firstOrCreate([
                'listing_id' => $id,
                'user_id' => $agentUser->id
            ]);
    
            return response()->json([
                'success' => true, 
                'message' => 'Agent délégué.',
                'needs_invite' => $isGhost,
                'mobile' => $mobile
            ]);
        }

        public function revokeAgent(Request $request, $id)
        {
            $listing = MarketplaceListing::findOrFail($id);
            if ($listing->user_id !== Auth::id()) return response()->json(['error' => 'Interdit'], 403);
    
            $request->validate(['user_id' => 'required']);
            MarketplaceAgent::where('listing_id', $id)->where('user_id', $request->user_id)->delete();
    
            return response()->json(['success' => true, 'message' => 'Révoqué.']);
        }

}