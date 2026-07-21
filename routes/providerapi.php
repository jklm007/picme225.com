<?php

use App\Http\Controllers\ProviderAuth\TokenController;
use App\Http\Controllers\ProviderResources\ProfileController;
use App\Http\Controllers\ProviderResources\TripController;
use App\Http\Controllers\ProviderResources\SharedRideController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProviderResources\DocumentController;
/*
|--------------------------------------------------------------------------
| Provider API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your provider application.
| These routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// --------------------------
// Authentication Routes (Public - No auth:api middleware)
// --------------------------

Route::get('/services', [TokenController::class, 'services']);
Route::get('/hospitals', [TokenController::class, 'hospitals']);
Route::post('/register', [TokenController::class, 'register']);
Route::post('/oauth/token', [TokenController::class, 'authenticate']); // Token request endpoint (login)
Route::post('/verify', [TokenController::class, 'verify']);
Route::post('/provider/otp', [TokenController::class, 'sendOtp']);
Route::post('/auth/facebook', [TokenController::class, 'facebookViaAPI']);
Route::post('/auth/google', [TokenController::class, 'googleViaAPI']);

Route::post('/forgot/password', [TokenController::class, 'forgot_password']);
Route::post('/reset/password', [TokenController::class, 'reset_password']);

// --- Wallet Public Callbacks ---
Route::post('/wallet/callback', [\App\Http\Controllers\ProviderResources\WalletController::class, 'callback']);
Route::get('/wallet/status', [\App\Http\Controllers\ProviderResources\WalletController::class, 'status']);

Route::get('/marketplace/categories', [\App\Http\Controllers\MarketplaceController::class, 'categories']);


// ----------------------------------------------------
// Protected Provider API Routes (Requires auth:api middleware)
// --------------------------------------


Route::group(['middleware' => ['auth:providerapi']], function () {
    Route::get('/ads/{slot}', [App\Http\Controllers\PrivateAdApiController::class, 'fetchAd']);
    Route::post('/ad/click', [App\Http\Controllers\PrivateAdApiController::class, 'recordClick']);
    // **CORRECT PREFIX: /api/provider** - Ajout du prefix '/provider' pour une URL correcte
    // Route::apiResource('documents', DocumentController::class);
//    Route::resource('documents', DocumentController::class)->only(['index', 'store']);

    // Route pour obtenir la liste des documents requis (index)
    Route::get('/documents', [DocumentController::class, 'index']);

    // Route pour stocker/télécharger de nouveaux documents (store)
    Route::post('/documents', [DocumentController::class, 'store']);

    // Route pour afficher un document spécifique (show) - Peut-être moins utilisée directement par l'Android, mais RESTful
    Route::get('/documents/{document}', [DocumentController::class, 'show']);

    // Route pour mettre à jour un document existant (update) - Peut-être utilisée si l'Android permet de remplacer un document
    Route::put('/documents/{document}', [DocumentController::class, 'update']); // Utilisez {document} comme paramètre
    Route::patch('/documents/{document}', [DocumentController::class, 'update']); // Route PATCH alternative pour mise à jour partielle

    // Route pour supprimer un document (destroy) - Peut-être moins utilisée dans l'application Android, mais RESTful
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']); // Utilisez {document} comme paramètre



    Route::post('/refresh/token', [TokenController::class, 'refresh_token']); // POST /api/provider/refresh/token
    Route::post('/logout', [TokenController::class, 'logout']);         // POST /api/provider/logout

    // --- Wallet Routes ---
    Route::get('/wallet/qr/generate', [\App\Http\Controllers\WalletTransferController::class, 'generateQrToken']);
    Route::get('/wallet/lookup', [\App\Http\Controllers\WalletTransferController::class, 'lookupByQr']);
    Route::get('/wallet', [\App\Http\Controllers\ProviderResources\WalletController::class, 'index']);
    Route::post('/wallet/recharge', [\App\Http\Controllers\ProviderResources\WalletController::class, 'recharge']);
    Route::post('/wallet/reward-ad', [\App\Http\Controllers\ProviderResources\WalletController::class, 'rewardAdMob']);
    Route::post('/wallet/transfer-to-eco', [\App\Http\Controllers\ProviderResources\WalletController::class, 'transferToEco']);
    Route::post('/wallet/transfer/scan', [\App\Http\Controllers\WalletTransferController::class, 'transferByScan']);
    Route::get('/wallet/lookup', [\App\Http\Controllers\WalletTransferController::class, 'lookupByQr']);
    Route::post('/wallet/withdraw', [\App\Http\Controllers\WalletTransferController::class, 'withdraw']);

    // --- Profile Routes ---
    Route::get('/profile', [ProfileController::class, 'index']);      // GET /api/provider/profile
    Route::post('/profile', [ProfileController::class, 'update']);     // POST /api/provider/profile
    Route::post('/password', [ProfileController::class, 'password']);   // POST /api/provider/profile/password
    Route::post('/location', [ProfileController::class, 'location']);   // POST /api/provider/profile/location
    Route::post('/available', [ProfileController::class, 'available'])->middleware('provider.eco');  // POST /api/provider/profile/available
    Route::get('/target', [ProfileController::class, 'target']);      // GET /api/provider/profile/target
    Route::post('/services', [ProfileController::class, 'update_service_selection']); // POST /api/provider/services
    Route::post('/smart-mode', [ProfileController::class, 'update_smart_mode']); // POST /api/provider/smart-mode
    Route::post('/gps-ping', [ProfileController::class, 'gps_ping']);             // [V2.3] POST /api/provider/gps-ping — Anti-Fraude MIS
    Route::get('/pdp-stops', [\App\Http\Controllers\UserServiceController::class, 'getPdpStops']); // GET /api/provider/pdp-stops
    Route::get('/pdp-routes', [\App\Http\Controllers\UserServiceController::class, 'getPdpRoutes']); // GET /api/provider/pdp-routes

    // --- Trip Routes ---
    Route::resource('/trip', 'ProviderResources\TripController');

    Route::post('/trip/cancel', [TripController::class, 'cancel']);         // POST /api/provider/trip/cancel - Annuler une course
    Route::post('/trip/summary', [TripController::class, 'summary']);        // POST /api/provider/trip/summary - Obtenir un résumé des courses
    Route::get('/trip/help', [TripController::class, 'help_details']);   // GET /api/provider/trip/help - Obtenir les informations d'aide
    Route::get('/support/chat', [TripController::class, 'getSupportHistory']);   // GET /api/provider/support/chat - Historique (SQL)
    Route::post('/support/chat', [TripController::class, 'sendSupportMessage']); // POST /api/provider/support/chat - Envoyer un message au support avec IA GROQ


    Route::post('/trip/{id}', [TripController::class, 'accept'])->middleware('provider.eco');           // POST /api/provider/trip/{id} - Accepter une course
    Route::post('/trip/{id}/decline-scheduled', [TripController::class, 'declineScheduled']); // POST /api/provider/trip/{id}/decline-scheduled - Abandonner une course planifiee
    Route::post('/trip/{id}/rate', [TripController::class, 'rate']);             // POST /api/provider/trip/{id}/rate - Noter une course (utilisateur)
    Route::post('/trip/{id}/message', [TripController::class, 'message']);          // POST /api/provider/trip/{id}/message - Envoyer un message (si applicable)
    Route::post('/trip/{id}/calculate', [TripController::class, 'calculate_distance']); // POST /api/provider/trip/{id}/calculate - Calculer la distance

    // --- Requests Routes ---
    Route::get('/requests/upcoming', [TripController::class, 'scheduled']);         // GET /api/provider/requests/upcoming - Lister les courses planifiées
    Route::get('/requests/history', [TripController::class, 'history']);           // GET /api/provider/requests/history - Lister l'historique des courses
    Route::get('/requests/history/details', [TripController::class, 'history_details']);   // GET /api/provider/requests/history/details - Détails d'une course historique
    Route::get('/requests/upcoming/details', [TripController::class, 'upcoming_details']);  // GET /api/provider/requests/upcoming/details - Détails d'une course planifiée

    // --- Shared Ride Routes (Service Partagé Instantané) ---
    Route::post('/shared/rides/start', [SharedRideController::class, 'startRide']);              // POST /api/provider/shared/rides/start
    Route::post('/shared/rides/{id}/update-position', [SharedRideController::class, 'updatePosition']); // POST /api/provider/shared/rides/{id}/update-position
    Route::post('/shared/rides/{id}/arrive-at-stop', [SharedRideController::class, 'arriveAtStop']);     // POST /api/provider/shared/rides/{id}/arrive-at-stop
    Route::post('/shared/rides/{id}/end', [SharedRideController::class, 'endRide']);                     // POST /api/provider/shared/rides/{id}/end
    Route::get('/shared/rides/current', [SharedRideController::class, 'getCurrentRide']);                // GET /api/provider/shared/rides/current

    // --- Subscription Routes ---
    Route::get('/subscriptions', 'ProviderResources\SubscriptionController@index');
    Route::post('/subscriptions/subscribe', 'ProviderResources\SubscriptionController@subscribe');

    // --- Insurance Claim Routes ---
    Route::get('/insurance/claims', 'ProviderResources\InsuranceClaimController@index');
    Route::post('/insurance/claims', 'ProviderResources\InsuranceClaimController@store');
    Route::get('/insurance/claims/{id}', 'ProviderResources\InsuranceClaimController@show');

    // --- Bonus Routes ---
    Route::get('/bonuses', 'ProviderResources\BonusController@index');                // GET /api/provider/bonuses - Historique des bonus
    Route::get('/bonuses/stats', 'ProviderResources\BonusController@stats');          // GET /api/provider/bonuses/stats - Statistiques détaillées
    Route::get('/bonuses/achievements', 'ProviderResources\BonusController@achievements'); // GET /api/provider/bonuses/achievements - Achievements/Milestones

    // --- DAO Routes for Providers ---
    Route::group(['prefix' => 'dao'], function () {
        Route::get('/proposals', [App\Http\Controllers\Dao\ProposalController::class, 'index']);
        Route::get('/proposals/{id}', [App\Http\Controllers\Dao\ProposalController::class, 'show']);
        Route::post('/proposals', [App\Http\Controllers\Dao\ProposalController::class, 'store']);
        Route::post('/proposals/{id}/vote', [App\Http\Controllers\Dao\ProposalController::class, 'vote']);
        Route::post('/proposals/{id}/execute', [App\Http\Controllers\Dao\ProposalController::class, 'execute']);
    });

    // --- SOCIAL TRANSPORT HUB (Feed commun) ---
    Route::group(['prefix' => 'social'], function () {
        // Parrainage (Referral)
        Route::get('/referral', [App\Http\Controllers\SocialTransportController::class, 'referral_info']);
        Route::post('/referral/apply', [App\Http\Controllers\SocialTransportController::class, 'apply_referral']);
    });

    // --- Notifications routes (sans préfixe social car désactivé) ---
    Route::get('/notifications', [App\Http\Controllers\SocialTransportController::class, 'notifications']);
    Route::get('/notifications/{id}', [App\Http\Controllers\SocialTransportController::class, 'showNotification']);
    Route::put('/notifications/{id}', [App\Http\Controllers\SocialTransportController::class, 'updateNotification']);
    Route::delete('/notifications/{id}', [App\Http\Controllers\SocialTransportController::class, 'destroyNotification']);
    Route::post('/notifications/mark-read', [App\Http\Controllers\SocialTransportController::class, 'markAllRead']);
    Route::post('/notifications/{id}/read', [App\Http\Controllers\SocialTransportController::class, 'markAsRead']);

    // =====================================================================
    // MARKETPLACE SOCIAL (VENTE & ANNONCES)
    // =====================================================================
    Route::group(['prefix' => 'marketplace'], function () {
        // Route::get('/categories', [App\Http\Controllers\MarketplaceController::class, 'categories']);
        Route::get('/', [App\Http\Controllers\MarketplaceController::class, 'index']);
        Route::post('/', [App\Http\Controllers\MarketplaceController::class, 'store']);
        
        // Dashboard marketplace (Must be before /{id})
        Route::get('/my-listings', [App\Http\Controllers\MarketplaceController::class, 'my_listings']);
        Route::get('/my-purchases', [App\Http\Controllers\MarketplaceController::class, 'myPurchases']);
        Route::get('/purchases/{id}/download', [App\Http\Controllers\MarketplaceController::class, 'downloadDigitalProduct']);
        Route::get('/my-bookings', [App\Http\Controllers\MarketplaceController::class, 'my_bookings']);
        Route::get('/received-bookings', [App\Http\Controllers\MarketplaceController::class, 'received_bookings']);

        // Panier
        Route::get('/cart',                        [App\Http\Controllers\MarketplaceController::class, 'getCart']);
        Route::post('/cart/{listingId}',           [App\Http\Controllers\MarketplaceController::class, 'addToCart']);
        Route::put('/cart/{listingId}',            [App\Http\Controllers\MarketplaceController::class, 'updateCartItem']);
        Route::delete('/cart/{listingId}',         [App\Http\Controllers\MarketplaceController::class, 'removeFromCart']);
        Route::delete('/cart',                     [App\Http\Controllers\MarketplaceController::class, 'clearCart']);

        Route::get('/{id}', [App\Http\Controllers\MarketplaceController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\MarketplaceController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\MarketplaceController::class, 'destroy']);
        Route::post('/{id}/agent-sell', [App\Http\Controllers\MarketplaceController::class, 'agentSellTicket']);
        Route::post('/{id}/buy', [App\Http\Controllers\MarketplaceController::class, 'buy']);
        Route::post('/{id}/rent', [App\Http\Controllers\MarketplaceController::class, 'rent']);
        Route::post('/{id}/rate', [App\Http\Controllers\MarketplaceController::class, 'rate']);
        Route::get('/{id}/tickets', [App\Http\Controllers\MarketplaceController::class, 'getListingTickets']);
        Route::post('/{id}/delegate', [App\Http\Controllers\MarketplaceController::class, 'delegateAgent']);
        Route::post('/{id}/revoke', [App\Http\Controllers\MarketplaceController::class, 'revokeAgent']);
        Route::post('/tickets/{ticket_id}/checkin', [App\Http\Controllers\MarketplaceController::class, 'manualCheckIn']);
        Route::get('/{id}/stats', [App\Http\Controllers\MarketplaceController::class, 'listingStats']);
        Route::get('/{id}/stats/export', [App\Http\Controllers\MarketplaceController::class, 'exportStats']);
        
        Route::post('/booking/{id}/status', [App\Http\Controllers\MarketplaceController::class, 'update_booking_status']);
        Route::post('/{id}/renew', [App\Http\Controllers\MarketplaceController::class, 'renew']);
        Route::post('/scan', [App\Http\Controllers\MarketplaceController::class, 'scan']);
        Route::post('/verify-picme-card', [App\Http\Controllers\MarketplaceController::class, 'verifyPicmeCard']);

        // Checkout / Livraison
        Route::post('/delivery/calculate',         [App\Http\Controllers\MarketplaceController::class, 'calculateDelivery']);
        Route::post('/checkout',                   [App\Http\Controllers\MarketplaceController::class, 'checkout']);
        Route::get('/track/{ref}',                 [App\Http\Controllers\MarketplaceController::class, 'trackOrder']);

        // Favoris
        Route::post('/{listingId}/favorite',       [App\Http\Controllers\MarketplaceController::class, 'toggleFavorite']);
        Route::get('/my-favorites',                [App\Http\Controllers\MarketplaceController::class, 'myFavorites']);
    });

}); // Fin du middleware group - protected routes
