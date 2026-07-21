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

class MarketplaceStatsController extends Controller
{
        public function listingStats($id)
        {
            $listing = MarketplaceListing::findOrFail($id);
            if ($listing->user_id !== Auth::id()) return response()->json(['error' => 'Non autorisé.'], 403);
            $tickets = TransportTicket::where('listing_id', $id)->get();
            $totalSales = $tickets->sum('total_price');
            $cashSales = $tickets->where('payment_mode', 'CASH')->sum('total_price');
            $walletSales = $tickets->where('payment_mode', 'WALLET')->sum('total_price');
            $user = Auth::user();
            $commissionPercent = ($user->user_badge === 'VIP') ? 0 : (($user->user_badge === 'PREMIUM') ? 5 : 10);
            return response()->json([
                'success' => true,
                'data' => [
                    'listing_title' => $listing->title,
                    'total_sold' => $tickets->count(),
                    'total_revenue' => $totalSales,
                    'cash_revenue' => $cashSales,
                    'wallet_revenue' => $walletSales,
                    'net_wallet_earnings' => $walletSales - ($walletSales * $commissionPercent / 100),
                    'commission_paid' => ($totalSales * $commissionPercent / 100),
                    'passes' => $listing->passes
                ]
            ]);
        }

        public function exportStats($id, Request $request)
        {
            if (!Auth::check() && $request->has('token')) {
                $user = \App\Models\User::where('access_token', $request->token)->first();
                if ($user) Auth::login($user);
            }
            $listing = MarketplaceListing::findOrFail($id);
            if ($listing->user_id !== Auth::id()) return response()->json(['error' => 'Non autorisé.'], 403);
            $tickets = TransportTicket::where('listing_id', $id)->with('user')->latest()->get();
            $filename = "Stats_" . Str::slug($listing->title) . "_" . date('Y-m-d') . ".csv";
            $headers = ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$filename"];
            $callback = function() use($tickets) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['ID', 'Client', 'Type', 'Prix', 'Mode', 'Date']);
                foreach ($tickets as $t) {
                    fputcsv($file, [$t->id, ($t->user ? $t->user->first_name : 'Inconnu'), 'Pass', $t->total_price, $t->payment_mode, $t->created_at]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

}