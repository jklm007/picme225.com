<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProviderAuth\TokenController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserServiceController;
use App\Http\Controllers\UserRideController;
use App\Http\Controllers\UserApiController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\UserHistoryController;
use App\Http\Controllers\UserPromoController;
use App\Http\Controllers\UserSharedController;
use App\Http\Controllers\UserSharedRideController;
use App\Http\Controllers\ProviderSharedController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Resource\CardResource;
use App\Http\Controllers\Resource\FavouriteLocationResource;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Http\Controllers\TransientTokenController;
use Laravel\Passport\Http\Controllers\PersonalAccessTokenController;


Route::group(['prefix' => 'oauth'], function () {
    Route::post('/token', [AccessTokenController::class, 'issueToken'])->name('passport.token');
    Route::get('/authorize', [AuthorizationController::class, 'authorize'])->name('passport.authorizations.authorize');
    Route::post('/token/refresh', [TransientTokenController::class, 'refreshToken'])->name('passport.token.refresh');
    Route::get('/personal-access-tokens', [PersonalAccessTokenController::class, 'index'])->name('passport.personal.tokens.index');
    Route::post('/personal-access-tokens', [PersonalAccessTokenController::class, 'store'])->name('passport.personal.tokens.store');
    Route::delete('/personal-access-tokens/{token_id}', [PersonalAccessTokenController::class, 'destroy'])->name('passport.personal.tokens.destroy');
});



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::post('/signin', [UserAuthController::class, 'signin'])->middleware('throttle:5,1');
Route::post('/signup', [UserAuthController::class, 'signup'])->middleware('throttle:5,1');

// WebRTC Signaling API (Internal check)
Route::post('/webrtc/verify-relation', [App\Http\Controllers\WebRTCController::class, 'verifyRelation']);
Route::post('/webrtc/trigger-push', [App\Http\Controllers\WebRTCController::class, 'triggerPush']);
Route::post('/webrtc/missed-call', [App\Http\Controllers\WebRTCController::class, 'missedCall']);

// Internal cluster route: Worker pod uploads images here so they are served by the web pod
// Protected by X-Internal-Secret header, NOT exposed to public internet via nginx config
Route::post('/internal/upload-image', [App\Http\Controllers\InternalUploadController::class, 'uploadImage']);



// NOUVELLE ROUTE: Connexion unifiée avec détection automatique du type de compte
Route::post('/unified-login', [App\Http\Controllers\UnifiedAuthController::class, 'unifiedLogin'])->middleware('throttle:100,1');
// Alias pour compatibilité app Android (appelle /api/user/unified-login)
Route::post('/user/unified-login', [App\Http\Controllers\UnifiedAuthController::class, 'unifiedLogin'])->middleware('throttle:100,1');


Route::post('/logout', [UserAuthController::class, 'logout']);
Route::post('/verify', [UserAuthController::class, 'verify']);
Route::post('/auth/facebook', [SocialLoginController::class, 'facebookViaAPI']);
Route::post('/auth/google', [SocialLoginController::class, 'googleViaAPI']);
// C'EST LA SYNTAXE CORRECTE (Laravel 8+)
Route::post('/provider/auth/google', [App\Http\Controllers\ProviderAuth\TokenController::class, 'googleViaAPI']);
Route::post('check/mobile', [UserAuthController::class, 'checkMobileExists']);
Route::post('/forgot/password', [UserAuthController::class, 'forgot_password'])->middleware('throttle:3,1');
Route::post('/reset/password', [UserAuthController::class, 'reset_password']);
Route::get('/package/rental', [UserServiceController::class, 'rental_package']);
Route::get('/service/rental', [UserServiceController::class, 'rental_service']);
Route::get('/ad/fetch', [App\Http\Controllers\PrivateAdApiController::class, 'fetchAd']);
        Route::get('/ads/{slot}', [App\Http\Controllers\PrivateAdApiController::class, 'fetchAd']);
Route::post('/ad/click', [App\Http\Controllers\PrivateAdApiController::class, 'recordClick']);

Route::get('/marketplace/categories', [MarketplaceController::class, 'categories']);

// GATEWAY SMS & ROBOT DE PAIEMENT (Automated P2P)
// Toutes les routes gateway sont protégées par le middleware gateway.auth
Route::group(['prefix' => 'gateway', 'middleware' => 'gateway.auth'], function () {
    // Routes Payment Robot
    Route::post('/sms-received', [App\Http\Controllers\SmsPaymentController::class, 'handleSms'])->middleware('throttle:30,1');
    Route::get('/pending-payouts', [App\Http\Controllers\SmsPaymentController::class, 'getPendingPayouts'])->middleware('throttle:60,1');
    Route::post('/confirm-payout', [App\Http\Controllers\SmsPaymentController::class, 'confirmPayout'])->middleware('throttle:10,1');
    Route::get('/active-receiver', [App\Http\Controllers\SmsPaymentController::class, 'getActiveReceiver'])->middleware('throttle:60,1');

    // Routes SMS Offline Booking (P2P Android Robot)
    Route::get('sms/outbox', [App\Http\Controllers\SmsBookingController::class, 'getOutbox']);
    Route::post('sms/outbox/confirm', [App\Http\Controllers\SmsBookingController::class, 'confirmSent']);
    Route::post('sms/inbox', [App\Http\Controllers\SmsBookingController::class, 'handleInbox']);
});

// Protected routes (require authentication)
Route::group(['middleware' => ['auth:api']], function () {

    // User profile
    Route::post('/change/password', [UserAuthController::class, 'change_password']);
    Route::post('/update/location', [UserProfileController::class, 'update_location']);
    Route::post('/user/pro/unlock', [App\Http\Controllers\UserProController::class, 'unlockPro']);
    Route::get('/marketplace', 'MarketplaceController@index');
    // Route::get('/marketplace/categories', 'MarketplaceController@categories');
    Route::get('/marketplace/my-purchases', 'MarketplaceController@myPurchases');
    Route::post('/marketplace', 'MarketplaceController@store');
    Route::match(['get', 'post'], '/details', [UserProfileController::class, 'details']);
    Route::post('/update/profile', [UserProfileController::class, 'update_profile']);
    Route::post('/update/kyc', [UserProfileController::class, 'updateKyc']);

    // Services
    Route::get('/services', [UserServiceController::class, 'services']);
    Route::get('/service-types', [UserServiceController::class, 'getServiceTypes']);

    // Antigravity — Moteur de filtrage intelligent des services par trajet
    // Paramètres : s_latitude, s_longitude, d_latitude, d_longitude
    // Retourne uniquement les services compatibles avec la zone du trajet :
    //   - Même commune     → COMMUNAL + INTERCOMMUNAL + TOUTE_ZONE
    //   - Communes diff.   → INTERCOMMUNAL + TOUTE_ZONE seulement
    //   - Coordonnées abs. → tout (mode dégradé gracieux)
    Route::get('/services/filter', [UserServiceController::class, 'getFilteredServices']);

    Route::get('/hospital_location', [UserServiceController::class, 'Hospital_based_location']);
    
    // NOUVEAU: Urgences et Voyages interurbains
    Route::get('/hospitals/nearby', [UserServiceController::class, 'nearbyHospitals']);
    Route::get('/travel/regional-routes', [UserServiceController::class, 'regionalRoutes']);

    // Vehicle Types (NEW - Filtrage intelligent)
    Route::get('/vehicle-types', [App\Http\Controllers\VehicleTypesApiController::class, 'getAllVehicleTypes']);
    Route::get('/services-with-vehicles', [App\Http\Controllers\VehicleTypesApiController::class, 'getServicesWithVehicles']);

    // Provider
    Route::post('/rate/provider', [UserRideController::class, 'rate_provider']);
    Route::post('/provider/otp', [App\Http\Controllers\ProviderAuth\TokenController::class, 'sendOtp'])->middleware('throttle:3,1');
    // Requests
    Route::post('/send/request', [UserRideController::class, 'send_request'])->middleware('check.subscription');
    Route::post('/cancel/request', [UserRideController::class, 'cancel_request']);
    Route::get('/request/check', [UserRideController::class, 'request_status_check']);
    Route::get('/show/providers', [UserRideController::class, 'show_providers']);
    Route::post('/update/request', [UserRideController::class, 'modifiy_request']);

    // History
    Route::get('/trips', [UserHistoryController::class, 'trips']);
    Route::get('/upcoming/trips', [UserHistoryController::class, 'upcoming_trips']);
    Route::get('/trip/details', [UserHistoryController::class, 'trip_details']);
    Route::get('/upcoming/trip/details', [UserHistoryController::class, 'upcoming_trip_details']);

    // Payment
    Route::post('/payment', [PaymentController::class, 'payment'])->middleware('throttle:10,1');
    Route::post('/request/{id}/confirm-payment', [UserRideController::class, 'confirmCashPayment']);
    Route::post('/add/money', [PaymentController::class, 'add_money'])->middleware('throttle:10,1');
    Route::get('/wallet/qr/generate', [App\Http\Controllers\WalletTransferController::class, 'generateQrToken'])->middleware('throttle:30,1');
    Route::get('/wallet/lookup', [App\Http\Controllers\WalletTransferController::class, 'lookupByQr'])->middleware('throttle:10,1');
    Route::post('/wallet/transfer/scan', [App\Http\Controllers\WalletTransferController::class, 'transferByScan'])->middleware('throttle:10,1');
    Route::post('/wallet/withdraw', [App\Http\Controllers\WalletTransferController::class, 'withdraw'])->middleware('throttle:10,1');

    // Estimated
    Route::get('/estimate/rental-fare', [UserServiceController::class, 'estimate_rental_fare']);
    Route::get('/estimated/fare', [UserServiceController::class, 'estimated_fare']);
    Route::post('/estimated/fare/delivery', [UserApiController::class, 'estimated_fare_delivery']);
    Route::post('/send/request/delivery', [UserApiController::class, 'send_delivery_request']);

    // Stop validation and search for stop-based rides (Protected)
    Route::get('/stops/nearby', [UserServiceController::class, 'getNearbyStops']);
    Route::get('/pdp/routes', [UserServiceController::class, 'getPdpRoutes']);
    Route::post('/stops/validate', [UserServiceController::class, 'validateStopLocation']);

    Route::group(['prefix' => 'shared'], function () {
        Route::get('shared/departure-board', 'UserSharedController@getDepartureBoard');
        Route::post('shared/ride', [UserSharedController::class, 'store']);
        Route::get('/route/{request_id}', [UserSharedController::class, 'routeDetails']);
        Route::get('/drivers/{request_id}', [UserSharedController::class, 'getDrivers']);
        Route::post('/add-passenger/{request_id}', [UserSharedController::class, 'addPassenger']);
        Route::delete('/remove-passenger/{request_id}/{passenger_id}', [UserSharedController::class, 'removePassenger']);
    });

    // Help
    Route::get('/help', [UserPromoController::class, 'help_details']);

    // Promocode
    Route::get('/promocodes', [UserPromoController::class, 'promocodes']);
    Route::post('/promocode/add', [UserPromoController::class, 'add_promocode']);

    // Partage / PDP Stops
    Route::get('/pdp-stops', [\App\Http\Controllers\PdpController::class, 'getNearbyPdp']);

    // Shared Ride Routes (Service Partagé Instantané)
    Route::get('/shared/rides/nearby', [UserSharedRideController::class, 'nearby']);                    // GET /api/user/shared/rides/nearby
    Route::post('/shared/rides/calculate-price', [UserSharedRideController::class, 'calculatePrice']);  // POST /api/user/shared/rides/calculate-price
    Route::post('/shared/rides/{rideId}/book', [UserSharedRideController::class, 'book']);              // POST /api/user/shared/rides/{rideId}/book
    Route::get('/shared/rides/bookings', [UserSharedRideController::class, 'myBookings']);              // GET /api/user/shared/rides/bookings
    Route::post('/shared/rides/bookings/{id}/cancel', [UserSharedRideController::class, 'cancelBooking']); // POST /api/user/shared/rides/bookings/{id}/cancel

    // Card Payment
    //   Route::apiResource('card', CardResource::class);
    Route::apiResource('card', CardResource::class)->names([
        'index' => 'custom_card.index',
        'store' => 'custom_card.store',
        'show' => 'custom_card.show',
        'update' => 'custom_card.update',
        'destroy' => 'custom_card.destroy',
    ]);

    // Favorite Locations
    Route::apiResource('location', FavouriteLocationResource::class);

    // Passbook
    Route::get('/wallet/passbook', [UserPromoController::class, 'wallet_passbook']);
    Route::get('/promo/passbook', [UserPromoController::class, 'promo_passbook']);

    // DAO (Gouvernance décentralisée)
    Route::group(['prefix' => 'dao'], function () {
        Route::get('/proposals', [App\Http\Controllers\Dao\ProposalController::class, 'index']);
        Route::get('/proposals/{id}', [App\Http\Controllers\Dao\ProposalController::class, 'show']);
        Route::post('/proposals', [App\Http\Controllers\Dao\ProposalController::class, 'store']);
        Route::post('/proposals/{id}/vote', [App\Http\Controllers\Dao\ProposalController::class, 'vote']);
        Route::post('/proposals/{id}/execute', [App\Http\Controllers\Dao\ProposalController::class, 'execute']);
    });

    // Token ECO
    Route::group(['prefix' => 'eco-token'], function () {
        Route::get('/balance', [App\Http\Controllers\EcoToken\TokenController::class, 'balance']);
        Route::get('/transactions', [App\Http\Controllers\EcoToken\TokenController::class, 'transactions']);
        Route::post('/transfer', [App\Http\Controllers\EcoToken\TokenController::class, 'transfer']);
        Route::post('/pay', [App\Http\Controllers\EcoToken\TokenController::class, 'payWithTokens']);
    });

    // Mobile Money
    Route::group(['prefix' => 'mobile-money', 'middleware' => 'throttle:15,1'], function () {
        Route::post('/payment/initiate', [App\Http\Controllers\MobileMoney\PaymentController::class, 'initiatePayment']);
        Route::get('/payment/verify/{transactionId}', [App\Http\Controllers\MobileMoney\PaymentController::class, 'verifyTransaction']);
        Route::get('/transactions', [App\Http\Controllers\MobileMoney\PaymentController::class, 'transactions']);
    });

    // Ad Campaigns
    Route::group(['prefix' => 'ad-campaigns'], function () {
        Route::get('/', [App\Http\Controllers\AdCampaignController::class, 'index']);
        Route::post('/', [App\Http\Controllers\AdCampaignController::class, 'store']);
        Route::get('/templates', [App\Http\Controllers\AdCampaignController::class, 'templates']);
        Route::post('/generate-content', [App\Http\Controllers\AdCampaignController::class, 'generateContent']);
        Route::get('/{id}', [App\Http\Controllers\AdCampaignController::class, 'show']);
        Route::post('/{id}/publish', [App\Http\Controllers\AdCampaignController::class, 'publish']);
        Route::get('/{id}/performance', [App\Http\Controllers\AdCampaignController::class, 'performance']);
    });

    // Fleet Owner Mobile Dashboard
    Route::group(['prefix' => 'fleet'], function () {
        Route::get('/dashboard', [App\Http\Controllers\Api\FleetApiController::class, 'dashboard']);
        Route::get('/drivers', [App\Http\Controllers\Api\FleetApiController::class, 'drivers']);
        Route::post('/withdraw', [App\Http\Controllers\Api\FleetApiController::class, 'withdraw']);
        Route::get('/withdrawals', [App\Http\Controllers\Api\FleetApiController::class, 'withdrawalHistory']);
        Route::post('/recharge-prepaid', [App\Http\Controllers\Api\FleetApiController::class, 'rechargePrepaid']);
    });

    // Unified Logistics & Colis Routes
    Route::group(['prefix' => 'logistics'], function () {
        Route::post('/quote', [App\Http\Controllers\Api\PackageController::class, 'quote']);
        Route::post('/create', [App\Http\Controllers\Api\PackageController::class, 'store']);
        Route::get('/track/{code}', [App\Http\Controllers\Api\PackageController::class, 'track']);
    });

    // Station Agent Logistics Routes
    Route::group(['prefix' => 'agent'], function () {
        Route::get('/packages', [App\Http\Controllers\Api\StationAgentApiController::class, 'packages']);
        Route::post('/process', [App\Http\Controllers\Api\StationAgentApiController::class, 'process']);
        Route::get('/summary', [App\Http\Controllers\Api\StationAgentApiController::class, 'summary']);
        Route::post('/withdraw', [App\Http\Controllers\Api\StationAgentApiController::class, 'withdraw']);

        // Cash Sales
        Route::post('/cash-booking', [App\Http\Controllers\Api\CashSaleController::class, 'createCashBooking']);
        Route::get('/cash-summary', [App\Http\Controllers\Api\CashSaleController::class, 'cashSummary']);

        // Ride Management — Gare de Compagnie (Vehicle Assignment)
        Route::get('/active-rides', [App\Http\Controllers\Api\StationAgentApiController::class, 'getActiveRides']);
        Route::post('/assign-ride', [App\Http\Controllers\Api\StationAgentApiController::class, 'assignRideToStation']);

        // Booker Urbain — Arrêt de Ville / Carrefour (Proxy Booking)
        Route::get('/available-services', [App\Http\Controllers\Api\StationAgentApiController::class, 'getAvailableServices']);
        Route::post('/proxy-booking', [App\Http\Controllers\Api\StationAgentApiController::class, 'proxyBooking']);
        Route::get('/proxy-booking/status', [App\Http\Controllers\Api\StationAgentApiController::class, 'proxyBookingStatus']);

        // Chargement Partagé - Wôrô-wôrôs
        Route::post('/shared/boarding/start', [App\Http\Controllers\Api\StationAgentSharedController::class, 'startBoarding']);
        Route::get('/shared/boarding', [App\Http\Controllers\Api\StationAgentSharedController::class, 'listBoardingRides']);
        Route::post('/shared/boarding/add-passenger', [App\Http\Controllers\Api\StationAgentSharedController::class, 'addPassengerToRide']);
        Route::post('/shared/boarding/{id}/dispatch', [App\Http\Controllers\Api\StationAgentSharedController::class, 'dispatchRide']);

        // Agent Événementiel (Scanner & Guichet)
        Route::get('/event/details', [App\Http\Controllers\Api\EventAgentController::class, 'getEventDetails']);
        Route::post('/event/scan', [App\Http\Controllers\Api\EventAgentController::class, 'scanTicket']);
        Route::post('/event/sell-cash', [App\Http\Controllers\Api\EventAgentController::class, 'sellCashTicket']);
    });


    // =====================================================================
    // SOCIAL TRANSPORT HUB - Routes du Réseau Social & Transport Communautaire
    // =====================================================================
    Route::group(['prefix' => 'social'], function () {

        // Parrainage (Referral)
        Route::get('/referral', [App\Http\Controllers\SocialTransportController::class, 'referral_info']);
        Route::post('/referral/apply', [App\Http\Controllers\SocialTransportController::class, 'apply_referral']);

        // Notifications
        Route::get('/notifications', [App\Http\Controllers\SocialTransportController::class, 'notifications']);
        Route::post('/notifications/bulk-delete', [App\Http\Controllers\SocialTransportController::class, 'bulkDestroyNotification']);
        Route::get('/notifications/{id}', [App\Http\Controllers\SocialTransportController::class, 'showNotification']);
        Route::put('/notifications/{id}', [App\Http\Controllers\SocialTransportController::class, 'updateNotification']);
        Route::delete('/notifications/{id}', [App\Http\Controllers\SocialTransportController::class, 'destroyNotification']);
        Route::post('/notifications/mark-read', [App\Http\Controllers\SocialTransportController::class, 'markAllRead']);
        Route::post('/notifications/{id}/read', [App\Http\Controllers\SocialTransportController::class, 'markAsRead']);

        // Système de Chat Sécurisé et Modéré
        Route::get('/secure-chat/contacts', [App\Http\Controllers\SecureChatController::class, 'contacts']);
        Route::post('/secure-chat/threads/bulk-delete', [App\Http\Controllers\SecureChatController::class, 'bulkDestroyThread']);
        Route::post('/secure-chat/{recipientId}/mark-read', [App\Http\Controllers\SecureChatController::class, 'markRead']);
        Route::get('/secure-chat/{recipientId}', [App\Http\Controllers\SecureChatController::class, 'thread']);
        Route::post('/secure-chat/{recipientId}', [App\Http\Controllers\SecureChatController::class, 'sendMessage']);
        Route::get('/secure-chat/messages/{id}', [App\Http\Controllers\SecureChatController::class, 'showMessage']);
        Route::put('/secure-chat/messages/{id}', [App\Http\Controllers\SecureChatController::class, 'updateMessage']);
        Route::delete('/secure-chat/messages/{id}', [App\Http\Controllers\SecureChatController::class, 'destroyMessage']);
        Route::delete('/secure-chat/thread/{recipientId}', [App\Http\Controllers\SecureChatController::class, 'destroyThread']);
        Route::post('/secure-chat/quote/{recipientId}', [App\Http\Controllers\SecureChatController::class, 'sendQuote']);
        Route::post('/secure-chat/quote/{id}/accept', [App\Http\Controllers\SecureChatController::class, 'acceptQuote']);
        Route::post('/secure-chat/quote/{id}/mark-completed', [App\Http\Controllers\SecureChatController::class, 'markQuoteCompleted']);
        Route::post('/secure-chat/quote/{id}/confirm', [App\Http\Controllers\SecureChatController::class, 'confirmQuote']);
        Route::post('/secure-chat/quote/{id}/dispute', [App\Http\Controllers\SecureChatController::class, 'openDispute']);
        Route::post('/secure-chat/quote/{id}/cancel-provider', [App\Http\Controllers\SecureChatController::class, 'cancelQuoteByProvider']);
        Route::post('/secure-chat/quote/{id}/cancel-client', [App\Http\Controllers\SecureChatController::class, 'cancelQuoteByClient']);
        // 🤖 PicMe AI - Statut du moteur IA
        Route::get('/ai/status', [App\Http\Controllers\SecureChatController::class, 'aiStatus']);

        // Fil Social principal (Corridor Feed)
        Route::get('/feed', [App\Http\Controllers\SocialTransportController::class, 'corridorFeed']);

        // Matching & Trajets Communautaires
        Route::get('/match', [App\Http\Controllers\SocialTransportController::class, 'findMatchingTrips']);
        Route::post('/trips', [App\Http\Controllers\SocialTransportController::class, 'createSocialTrip']);
        Route::post('/intentions', [App\Http\Controllers\SocialTransportController::class, 'createIntention']);
        Route::post('/intentions/{id}/pledge', [App\Http\Controllers\SocialTransportController::class, 'pledge']);
        Route::post('/posts/{id}/join', [App\Http\Controllers\SocialTransportController::class, 'bookTrip']);

        // Interactions sociales (Like, Dislike, Comment, Share, Favorite, Delete, SOS)
        Route::post('/posts/{id}/like', [App\Http\Controllers\SocialTransportController::class, 'toggleLike']);
        Route::post('/posts/{id}/dislike', [App\Http\Controllers\SocialTransportController::class, 'toggleDislike']);
        Route::post('/posts/{id}/comment', [App\Http\Controllers\SocialTransportController::class, 'comment']);
        Route::get('/posts/{id}/comments', [App\Http\Controllers\SocialTransportController::class, 'getComments']);
        Route::post('/posts/{id}/share', [App\Http\Controllers\SocialTransportController::class, 'incrementShare']);
        Route::post('/posts/{id}/favorite', [App\Http\Controllers\SocialTransportController::class, 'toggleFavorite']);
        Route::delete('/posts/{id}/delete', [App\Http\Controllers\SocialTransportController::class, 'deleteMyPost']);
        Route::post('/posts/{id}/sos', [App\Http\Controllers\SocialTransportController::class, 'triggerSos']);

        // Sondages (Polls)
        Route::post('/polls/{id}/vote', [App\Http\Controllers\SocialTransportController::class, 'votePoll']);

        // Stories et Profils
        Route::get('/stories', [App\Http\Controllers\SocialTransportController::class, 'getStories']);
        Route::get('/users/search', [App\Http\Controllers\SocialTransportController::class, 'searchUsers']);
        Route::get('/users/{id}/profile', [App\Http\Controllers\SocialTransportController::class, 'showMemberProfile']);
        Route::post('/posts/user', [App\Http\Controllers\SocialTransportController::class, 'createUserPost']);
        Route::post('/author/favorite', [App\Http\Controllers\SocialTransportController::class, 'toggleFavoriteAuthor']);

        // Billetterie Sociale (Events & E-Tickets)
        Route::get('/tickets/events', [App\Http\Controllers\SocialTicketController::class, 'getActiveEvents']);
        Route::post('/tickets/events/{id}/buy', [App\Http\Controllers\SocialTicketController::class, 'buyTicket']);
        Route::get('/tickets/events/{id}/sync', [App\Http\Controllers\SocialTicketController::class, 'syncTickets']);
    });

    // =====================================================================
    // LOCATION DE VEHICULES SANS CHAUFFEUR
    // =====================================================================
    Route::group(['prefix' => 'rental'], function () {
        Route::get('/', [App\Http\Controllers\VehicleRentalController::class, 'index']);
        Route::post('/{listingId}/book', [App\Http\Controllers\VehicleRentalController::class, 'book']);
        Route::post('/bookings/{bookingId}/accept', [App\Http\Controllers\VehicleRentalController::class, 'acceptBooking']);
        Route::post('/bookings/{bookingId}/reject', [App\Http\Controllers\VehicleRentalController::class, 'rejectBooking']);
        Route::post('/bookings/{bookingId}/complete', [App\Http\Controllers\VehicleRentalController::class, 'complete']);
    });

    // =====================================================================
    // MARKETPLACE SOCIAL (VENTE & ANNONCES)
    // =====================================================================
    Route::group(['prefix' => 'marketplace'], function () {
        Route::get('/categories', [App\Http\Controllers\MarketplaceController::class, 'categories']);
        Route::get('/', [App\Http\Controllers\MarketplaceController::class, 'index']);
        Route::post('/', [App\Http\Controllers\MarketplaceController::class, 'store']);
        
        // Dashboard marketplace (Must be before /{id})
        Route::get('/my-listings', [App\Http\Controllers\MarketplaceController::class, 'my_listings']);
        Route::get('/my-purchases', [App\Http\Controllers\MarketplaceController::class, 'myPurchases']);
        Route::get('/purchases/{id}/download', [App\Http\Controllers\MarketplaceController::class, 'downloadDigitalProduct']);
        Route::get('/my-bookings', [App\Http\Controllers\MarketplaceController::class, 'my_bookings']);
        Route::get('/received-bookings', [App\Http\Controllers\MarketplaceController::class, 'received_bookings']);
        
        // ── 🛒 PANIER (CART) ─────────────────────────────────────────────
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

        // ── 🚚 CALCUL LIVRAISON ──────────────────────────────────────────
        Route::post('/delivery/calculate',         [App\Http\Controllers\MarketplaceController::class, 'calculateDelivery']);

        // ── ✅ COMMANDE (CHECKOUT) ────────────────────────────────────────
        Route::post('/checkout',                   [App\Http\Controllers\MarketplaceController::class, 'checkout']);

        // ── 📦 SUIVI COMMANDE ────────────────────────────────────────────
        Route::get('/track/{ref}',                 [App\Http\Controllers\MarketplaceController::class, 'trackOrder']);

        // ── ❤️ FAVORIS ───────────────────────────────────────────────────
        Route::post('/{listingId}/favorite',       [App\Http\Controllers\MarketplaceController::class, 'toggleFavorite']);
        Route::get('/my-favorites',                [App\Http\Controllers\MarketplaceController::class, 'myFavorites']);
    });

    // PORTEFEUILLE (WALLET)
    // =====================================================================
    Route::group(['prefix' => 'wallet', 'middleware' => 'throttle:15,1'], function () {
        Route::post('/add', [App\Http\Controllers\WalletController::class, 'add_money']);
        Route::post('/transfer-to-eco', [App\Http\Controllers\WalletController::class, 'transferToEco']);
        Route::post('/reward-ad', [App\Http\Controllers\WalletController::class, 'rewardAdMob']);
        Route::get('/passbook', [App\Http\Controllers\WalletController::class, 'passbook']);
        Route::post('/verify-recharge', [App\Http\Controllers\SmsPaymentController::class, 'verifyManualRecharge']);
    });

    // =====================================================================
    // ABONNEMENTS TRANSPORT — Trajet Récurrent Dynamique (Prix calculé en temps réel)
    // =====================================================================
    Route::group(['prefix' => 'subscription'], function () {
        // ── Nouveau système dynamique ──────────────────────────────────────
        Route::post('/estimate',  [App\Http\Controllers\UserSubscriptionController::class, 'estimate']);
        Route::post('/purchase',  [App\Http\Controllers\UserSubscriptionController::class, 'purchase']);
        Route::get('/status',     [App\Http\Controllers\UserSubscriptionController::class, 'status']);
        Route::get('/schedule',   [App\Http\Controllers\UserSubscriptionController::class, 'getSchedule']);
        Route::post('/schedule',  [App\Http\Controllers\UserSubscriptionController::class, 'updateSchedule']);
        Route::post('/cancel',    [App\Http\Controllers\UserSubscriptionController::class, 'cancel']);
        Route::get('/history',    [App\Http\Controllers\UserSubscriptionController::class, 'history']);

        // ── Backward-compat aliases (deprecated — kept during mobile app transition) ─
        Route::get('/plans',           [App\Http\Controllers\UserSubscriptionController::class, 'getSubscriptionPlans']);
        Route::get('/status-legacy',   [App\Http\Controllers\UserSubscriptionController::class, 'getSubscriptionStatus']);
        Route::post('/purchase-legacy',[App\Http\Controllers\UserSubscriptionController::class, 'purchaseSubscription']);
        Route::get('/schedule-legacy', [App\Http\Controllers\UserSubscriptionController::class, 'getSubscriptionSchedule']);
        Route::post('/schedule-legacy',[App\Http\Controllers\UserSubscriptionController::class, 'saveSubscriptionSchedule']);
    });

    // =====================================================================
    // ABONNEMENTS MARKETPLACE — Plans Vendeur/Marchand à Prix Fixe
    // =====================================================================
    Route::group(['prefix' => 'marketplace/subscription'], function () {
        Route::get('/plans',    [App\Http\Controllers\MarketplaceSubscriptionController::class, 'plans']);
        Route::post('/purchase',[App\Http\Controllers\MarketplaceSubscriptionController::class, 'purchase']);
        Route::get('/status',   [App\Http\Controllers\MarketplaceSubscriptionController::class, 'status']);
    });

    // =====================================================================
    // LISTE D'ATTENTE DE SERVICE (Waitlist System)
    // =====================================================================
    Route::group(['prefix' => 'waitlist'], function () {
        Route::post('/join',   [App\Http\Controllers\UserWaitlistController::class, 'join']);
        Route::post('/leave',  [App\Http\Controllers\UserWaitlistController::class, 'leave']);
        Route::get('/status',  [App\Http\Controllers\UserWaitlistController::class, 'status']);
    });


    // ==========================================
    // SMART ROUTE (Routage Intelligent Tri-Modal)
    // ==========================================
    Route::get('/smart-route', [App\Http\Controllers\SmartRouteController::class, 'get_smart_route']);

    // =====================================================================
    // ACTUALITÉS RSS (Flux Ivoiriens Agrégés - Abidjan.net, KOACI, RTI...)
    // =====================================================================
    Route::get('/news-feed', [App\Http\Controllers\NewsAggregatorController::class, 'index']);
    Route::get('/news-sources', [App\Http\Controllers\NewsAggregatorController::class, 'sources']);

});

// Webhooks Mobile Money (sans authentification, mais avec signature)
Route::post('/mobile-money/webhook/{provider}', [App\Http\Controllers\MobileMoney\PaymentController::class, 'webhook']);

// Webhook CinetPay IPN — Recharge portefeuille Utilisateur
// (URL configurée dans notify_url lors de la création du paiement)
Route::post('/wallet/webhook/cinetpay', [App\Http\Controllers\WalletController::class, 'cinetpayWebhook']);

// Webhook Wave — Recharge portefeuille Utilisateur
Route::post('/wallet/webhook/wave', [App\Http\Controllers\WalletController::class, 'waveWebhook']);


Route::group(['prefix' => 'provider/shared', 'middleware' => ['auth:providerapi']], function () {
    Route::post('/accept/{request_id}', [ProviderSharedController::class, 'accept']);
    Route::post('/reject/{request_id}', [ProviderSharedController::class, 'reject']);
});


// =========================================================================
// ZONES ACTIVES - Carte de chaleur pour l'application User
// =========================================================================
Route::get('/user/active-zones', [App\Http\Controllers\User\HeatmapController::class, 'activeZones'])->middleware('auth:api');

// Global Logger for debugging 404s
Route::post('/whatsapp/webhook', [App\Http\Controllers\Api\WhatsAppWebhookController::class, 'handle']);

Route::any('{any}', function ($any) {
    \Log::warning("UNDEFINED ROUTE: " . request()->method() . " " . request()->fullUrl());
    return response()->json(['error' => 'Route non définie sur le serveur.'], 404);
})->where('any', '.*');
