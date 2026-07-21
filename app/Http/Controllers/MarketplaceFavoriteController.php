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

class MarketplaceFavoriteController extends Controller
{
        public function toggleFavorite(Request $request, $listingId): JsonResponse
        {
            Log::info("Marketplace: Toggling favorite", ['user_id' => Auth::id(), 'listing_id' => $listingId]);
            $user = $request->user();
            $favs = json_decode($user->marketplace_favorites ?? '[]', true) ?? [];
    
            if (in_array((int) $listingId, $favs)) {
                $favs = array_values(array_diff($favs, [(int) $listingId]));
                $favorited = false;
            } else {
                $favs[] = (int) $listingId;
                $favorited = true;
            }
    
            $user->update(['marketplace_favorites' => json_encode($favs)]);
    
            return response()->json([
                'success'   => true,
                'favorited' => $favorited,
                'message'   => $favorited ? '❤️ Ajouté aux favoris' : '🖤 Retiré des favoris',
            ]);
        }

        public function myFavorites(Request $request): JsonResponse
        {
            $user = $request->user();
            $favIds = json_decode($user->marketplace_favorites ?? '[]', true) ?? [];
    
            if (empty($favIds)) {
                return response()->json(['success' => true, 'data' => []]);
            }
    
            $listings = MarketplaceListing::with('user:id,first_name,last_name,display_name,picture')
                ->whereIn('id', $favIds)
                ->where('status', 'ACTIVE')
                ->get()
                ->map(function($item) {
                    if ($item->cover_image && !str_starts_with($item->cover_image, 'http')) {
                        $item->cover_image = \Storage::disk('s3')->url( $item->cover_image);
                    }
                    return $item;
                });
    
            return response()->json(['success' => true, 'data' => $listings]);
        }

}