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

/**
 * Backward-compatible facade for MarketplaceController.
 */
class MarketplaceController extends Controller
{
    public function index(Request $request) { return (new MarketplaceListingController())->index($request); }
    public function categories() { return (new MarketplaceListingController())->categories(); }
    public function store(Request $request) { return (new MarketplaceListingController())->store($request); }
    public function update(Request $request, $id) { return (new MarketplaceListingController())->update($request, $id); }
    public function show(Request $request, $id) { return (new MarketplaceListingController())->show($request, $id); }
    public function destroy($id) { return (new MarketplaceListingController())->destroy($id); }
    public function rate(Request $request, $id) { return (new MarketplaceListingController())->rate($request, $id); }
    public function my_listings() { return (new MarketplaceListingController())->my_listings(); }
    public function renew($id) { return (new MarketplaceListingController())->renew($id); }
    public function getCart(Request $request) { return (new MarketplaceCartController())->getCart($request); }
    public function addToCart(Request $request, $listingId) { return (new MarketplaceCartController())->addToCart($request, $listingId); }
    public function updateCartItem(Request $request, $listingId) { return (new MarketplaceCartController())->updateCartItem($request, $listingId); }
    public function removeFromCart(Request $request, $listingId) { return (new MarketplaceCartController())->removeFromCart($request, $listingId); }
    public function clearCart(Request $request) { return (new MarketplaceCartController())->clearCart($request); }
    public function calculateDelivery(Request $request) { return (new MarketplaceCartController())->calculateDelivery($request); }
    public function checkout(Request $request) { return (new MarketplaceCartController())->checkout($request); }
    public function trackOrder(Request $request, $ref) { return (new MarketplaceCartController())->trackOrder($request, $ref); }
    public function buy(Request $request, $id) { return (new MarketplaceBookingController())->buy($request, $id); }
    public function rent(Request $request, $id) { return (new MarketplaceBookingController())->rent($request, $id); }
    public function my_bookings() { return (new MarketplaceBookingController())->my_bookings(); }
    public function received_bookings() { return (new MarketplaceBookingController())->received_bookings(); }
    public function update_booking_status(Request $request, $id) { return (new MarketplaceBookingController())->update_booking_status($request, $id); }
    public function myPurchases(Request $request) { return (new MarketplaceBookingController())->myPurchases($request); }
    public function downloadDigitalProduct(Request $request, $id) { return (new MarketplaceBookingController())->downloadDigitalProduct($request, $id); }
    public function scan(Request $request) { return (new MarketplaceTicketController())->scan($request); }
    public function verifyPicmeCard(Request $request) { return (new MarketplaceTicketController())->verifyPicmeCard($request); }
    public function publicTicketView($signature) { return (new MarketplaceTicketController())->publicTicketView($signature); }
    public function getListingTickets($id) { return (new MarketplaceTicketController())->getListingTickets($id); }
    public function manualCheckIn($ticket_id) { return (new MarketplaceTicketController())->manualCheckIn($ticket_id); }
    public function agentSellTicket(Request $request, $id) { return (new MarketplaceTicketController())->agentSellTicket($request, $id); }
    public function delegateAgent(Request $request, $id) { return (new MarketplaceAgentController())->delegateAgent($request, $id); }
    public function revokeAgent(Request $request, $id) { return (new MarketplaceAgentController())->revokeAgent($request, $id); }
    public function listingStats($id) { return (new MarketplaceStatsController())->listingStats($id); }
    public function exportStats($id, Request $request) { return (new MarketplaceStatsController())->exportStats($id, $request); }
    public function toggleFavorite(Request $request, $listingId) { return (new MarketplaceFavoriteController())->toggleFavorite($request, $listingId); }
    public function myFavorites(Request $request) { return (new MarketplaceFavoriteController())->myFavorites($request); }
}