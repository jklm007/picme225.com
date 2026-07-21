<?php

/*
|--------------------------------------------------------------------------
| User Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/offline', function () {
    return view('offline');
});


Route::get('/ticket/view/{booking_id}', [App\Http\Controllers\Api\DigitalTicketController::class, 'show'])->name('ticket.view');

Route::get('/ticket/{signature}', [App\Http\Controllers\MarketplaceController::class, 'publicTicketView'])->name('ticket.public');

/*
|--------------------------------------------------------------------------
| WhatsApp QR Code (Evolution API) Ã¢â‚¬â€ routes dÃƒÂ©placÃƒÂ©es dans admin.php
|--------------------------------------------------------------------------
| AccÃƒÂ¨s sÃƒÂ©curisÃƒÂ© via : /admin/whatsapp-listings/connect
|--------------------------------------------------------------------------
*/

Auth::routes();
Route::get('/ride', 'Auth\SocialLoginController@ride_val');
Route::get('auth/facebook', 'Auth\SocialLoginController@redirectToFaceBook');
Route::get('auth/facebook/callback', 'Auth\SocialLoginController@handleFacebookCallback');
Route::get('auth/google', 'Auth\SocialLoginController@redirectToGoogle');
Route::get('auth/google/callback', 'Auth\SocialLoginController@handleGoogleCallback');
Route::post('account/kit', 'Auth\SocialLoginController@account_kit')->name('account.kit');
Route::post('/verify-phone', 'Auth\RegisterController@verifyPhone')->name('verify.phone');

/*
|--------------------------------------------------------------------------
| Provider Authentication Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'provider'], function () {

    Route::get('auth/facebook', 'Auth\SocialLoginController@providerToFaceBook');
    Route::get('auth/google', 'Auth\SocialLoginController@providerToGoogle');

    Route::get('/login', 'ProviderAuth\LoginController@showLoginForm')->name('provider.login');
    Route::get('/register', 'ProviderAuth\RegisterController@showRegistrationForm')->name('provider.register');
    Route::post('/login', 'ProviderAuth\LoginController@login');
    Route::post('/logout', 'ProviderAuth\LoginController@logout');
    Route::post('/register', 'ProviderAuth\RegisterController@register');

    Route::post('/password/email', 'ProviderAuth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('/password/reset', 'ProviderAuth\ResetPasswordController@reset');
    Route::post('/password/reset/otp', 'ProviderAuth\ForgotPasswordController@resetViaOtp');
    Route::get('/password/reset', 'ProviderAuth\ForgotPasswordController@showLinkRequestForm');
    Route::get('/password/reset/{token}', 'ProviderAuth\ResetPasswordController@showResetForm');
});

/*
|--------------------------------------------------------------------------
| Admin Authentication Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'admin'], function () {
    Route::get('/login', 'AdminAuth\LoginController@showLoginForm');
    Route::post('/login', 'AdminAuth\LoginController@login');
    Route::post('/logout', 'AdminAuth\LoginController@logout');

    Route::post('/password/email', 'AdminAuth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('/password/reset', 'Auth\ResetPasswordController@reset');
    Route::post('/password/reset/otp', 'Auth\ForgotPasswordController@resetViaOtp');
    Route::get('/password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm');
    Route::get('/password/reset/{token}', 'AdminAuth\ResetPasswordController@showResetForm');
});

/*
|--------------------------------------------------------------------------
| Dispatcher Authentication Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'dispatcher'], function () {
    Route::get('/login', 'DispatcherAuth\LoginController@showLoginForm');
    Route::post('/login', 'DispatcherAuth\LoginController@login');
    Route::post('/logout', 'DispatcherAuth\LoginController@logout');

    Route::post('/password/email', 'DispatcherAuth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('/password/reset', 'DispatcherAuth\ResetPasswordController@reset');
    Route::get('/password/reset', 'DispatcherAuth\ForgotPasswordController@showLinkRequestForm');
    Route::get('/password/reset/{token}', 'DispatcherAuth\ResetPasswordController@showResetForm');
});

/*
|--------------------------------------------------------------------------
| Fleet Authentication Routes
|--------------------------------------------------------------------------
*/


Route::group(['prefix' => 'fleet'], function () {
    Route::get('/login', 'FleetAuth\LoginController@showLoginForm');
    Route::post('/login', 'FleetAuth\LoginController@login');
    Route::post('/logout', 'FleetAuth\LoginController@logout');

    Route::post('/password/email', 'FleetAuth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('/password/reset', 'FleetAuth\ResetPasswordController@reset');
    Route::get('/password/reset', 'FleetAuth\ForgotPasswordController@showLinkRequestForm');
    Route::get('/password/reset/{token}', 'FleetAuth\ResetPasswordController@showResetForm');
});

/*
|--------------------------------------------------------------------------
| Account Authentication Routes
|--------------------------------------------------------------------------
*/


Route::group(['prefix' => 'account'], function () {
    Route::get('/login', 'AccountAuth\LoginController@showLoginForm');
    Route::post('/login', 'AccountAuth\LoginController@login');
    Route::post('/logout', 'AccountAuth\LoginController@logout');

    Route::get('/register', 'AccountAuth\RegisterController@showRegistrationForm');
    Route::post('/register', 'AccountAuth\RegisterController@register');

    Route::post('/password/email', 'AccountAuth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('/password/reset', 'AccountAuth\ResetPasswordController@reset');
    Route::get('/password/reset', 'AccountAuth\ForgotPasswordController@showLinkRequestForm');
    Route::get('/password/reset/{token}', 'AccountAuth\ResetPasswordController@showResetForm');
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'language'], function () {
    Route::get('/', 'HomeController@index');
    Route::get('/marketplace', 'HomeController@marketplace')->name('marketplace.public');
    Route::get('/marketplace/{id}', 'HomeController@marketplace_detail')->name('marketplace.detail');
    Route::get('/location', 'RentalController@index')->name('rental.location.index');
    Route::get('/estimate/fare', 'HomeController@estimate_fare');
    Route::post('/lang', 'Auth\RegisterController@lang');
});

// Serve marketplace images from DB (base64) Ã¢â‚¬â€ survives K8s pod restarts
Route::get('/marketplace-img/{id}/{index}', function ($id, $index = 0) {
    $listing = \App\Models\MarketplaceListing::find($id);
    if (!$listing) abort(404);

    // Build ordered image list
    $allImgs = [];
    if ($listing->cover_image) $allImgs[] = $listing->cover_image;
    if (is_array($listing->images)) {
        foreach ($listing->images as $img) {
            if ($img && $img !== $listing->cover_image) $allImgs[] = $img;
        }
    }

    $src = $allImgs[(int)$index] ?? ($allImgs[0] ?? null);
    if (!$src) abort(404);

    // If it's a file path on disk, serve it
    if (!str_starts_with($src, 'data:') && !str_starts_with($src, 'http')) {
        $path = storage_path('app/public/' . $src);
        if (file_exists($path)) {
            return response()->file($path, ['Content-Type' => 'image/webp', 'Cache-Control' => 'public, max-age=86400']);
        }
        // File missing Ã¢â‚¬â€ try to serve from whatsapp_message medias
        $msg = $listing->whatsappMessage;
        if ($msg && !empty($msg->medias)) {
            $medias = is_array($msg->medias) ? $msg->medias : json_decode($msg->medias, true);
            $src = $medias[(int)$index] ?? ($medias[0] ?? null);
            if (!$src) abort(404);
        } else {
            abort(404);
        }
    }

    // Parse base64 data URI
    if (preg_match('/^data:image\/(\w+);base64,(.+)$/s', $src, $m)) {
        $type = $m[1];
        $data = base64_decode($m[2]);
        return response($data, 200, [
            'Content-Type'  => 'image/' . $type,
            'Cache-Control' => 'public, max-age=86400',
            'Content-Length'=> strlen($data),
        ]);
    }

    // External URL Ã¢â‚¬â€ redirect
    return redirect($src);
})->name('marketplace.image');
// Route POST /login maintenue pour usage interne (formulaires redirigeant explicitement vers cette route)
Route::post('/login', 'Auth\LoginController@login');
// DÃƒÂ©blocage de la route login pour la page unifiÃƒÂ©e web
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');

Route::get('/register', 'Auth\RegisterController@showRegistrationForm')->name('register');

Route::get('/initsetup', function () {
    return Setting::all();
});

// Route publique pour lire un article complet avec un ID ou Slug unique
Route::get('/article/{id}', 'SocialTransportController@showArticle')->name('article.show');

/*Route::get('/ride', function () {
    return view('ride');
});*/


// Route /drive reactivee vers sa landing page premium
Route::get('/drive', function () {
    return view('drive');
});

/*
|--------------------------------------------------------------------------
| APK Download Routes Ã¢â‚¬â€ En attente des liens Google Play/App Store
| Les fichiers APK doivent ÃƒÂªtre placÃƒÂ©s dans public/uploads/apk/
|--------------------------------------------------------------------------
*/
Route::get('/download/user', function () {
    $playStoreLink = Setting::get('store_link_android');
    if ($playStoreLink) {
        return redirect($playStoreLink);
    }
    $apkPath = public_path('uploads/apk/picme225-user.apk');
    if (file_exists($apkPath)) {
        return response()->download($apkPath, 'PicMe225-Client.apk');
    }
    return redirect('/')->with('info', 'L\'application sera disponible trÃƒÂ¨s bientÃƒÂ´t sur Google Play.');
})->name('download.user');

Route::get('/download/driver', function () {
    $playStoreLink = Setting::get('provider_store_link_android');
    if ($playStoreLink) {
        return redirect($playStoreLink);
    }
    $apkPath = public_path('uploads/apk/picme225-driver.apk');
    if (file_exists($apkPath)) {
        return response()->download($apkPath, 'PicMe225-Driver.apk');
    }
    return redirect('/drive')->with('info', 'L\'application Driver sera disponible trÃƒÂ¨s bientÃƒÂ´t sur Google Play.');
})->name('download.driver');



Route::get('/privacy', function () {
    return view('privacy');
});

Route::get('/help', function () {
    return view('help');
});


/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/

// Photon Autocomplete Proxy (accessible depuis le navigateur, proxifie vers le serveur interne K8s)
Route::get('/places/search', function (\Illuminate\Http\Request $request) {
    $q   = $request->input('q', '');
    $lat = $request->input('lat', '5.3599517');
    $lng = $request->input('lng', '-4.0082563');
    $limit = $request->input('limit', '10');
    if (strlen($q) < 1) return response()->json(['features' => []]);

    $mapboxUrl = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' . urlencode($q) . '.json';
    $url = $mapboxUrl . '?access_token=' . env('MAPBOX_API_KEY')
         . '&proximity=' . $lng . ',' . $lat
         . '&country=ci&language=fr&limit=' . $limit . '&autocomplete=true';

    try {
        $response = \Illuminate\Support\Facades\Http::timeout(4)->get($url);
        $data = $response->json();
        
        $features = [];
        if (isset($data['features'])) {
            foreach ($data['features'] as $f) {
                $city = '';
                if (isset($f['context'])) {
                    foreach ($f['context'] as $c) {
                        if (str_starts_with($c['id'], 'place') || str_starts_with($c['id'], 'region')) {
                            $city = $c['text'] ?? '';
                            break;
                        }
                    }
                }
                $features[] = [
                    'geometry' => [
                        'coordinates' => $f['center'] ?? ($f['geometry']['coordinates'] ?? [0,0])
                    ],
                    'properties' => [
                        'name' => $f['text'] ?? '',
                        'city' => $city,
                        'street' => $f['place_name'] ?? ''
                    ]
                ];
            }
        }
        
        return response()->json(['features' => $features]);
    } catch (\Exception $e) {
        return response()->json(['features' => []]);
    }
});

Route::group(['middleware' => 'auth'], function () {
    // Nouvel Accueil
    Route::get('/home', 'UserDashboardController@home')->name('home');
    Route::get('/dashboard', 'UserDashboardController@index');
    Route::get('/notifications', 'UserDashboardController@notifications');
Route::get('/hour/{id}', 'UserServiceController@pricing_logic');

// user profiles
Route::get('/profile', 'UserDashboardController@profile');
Route::get('/edit/profile', 'UserDashboardController@edit_profile');
Route::post('/profile', 'UserDashboardController@update_profile');
Route::get('/dashboard/status', 'UserDashboardController@check_dash');

// update password
Route::get('/change/password', 'UserDashboardController@change_password');
Route::post('/change/password', 'UserDashboardController@update_password');

// ride
Route::get('/confirm/ride', 'RideController@confirm_ride');
Route::post('/create/ride', 'RideController@create_ride');
Route::post('/cancel/ride', 'RideController@cancel_ride');
Route::get('/onride', 'RideController@onride');
Route::post('/payment', 'PaymentController@payment');
Route::post('/rate', 'RideController@rate');

// status check
Route::get('/status', 'RideController@status');

// trips
Route::get('/trips', 'UserDashboardController@trips');
Route::get('/upcoming/trips', 'UserDashboardController@upcoming_trips');

// wallet
Route::get('/wallet', 'UserDashboardController@wallet');
Route::post('/add/money', 'PaymentController@add_money');

// payment
Route::get('/payment', 'UserDashboardController@payment');

// card
//Route::resource('card', 'Resource\CardResource');

Route::resource('card', 'Resource\CardResource')->names([
    'index' => 'user.card.index',
    'store' => 'user.card.store',
    'destroy' => 'user.card.destroy',
    // autres noms personnalisÃ¢â€Å“Ã‚Â®s
]);

// promotions
Route::get('/promotions', 'UserDashboardController@promotions_index')->name('promocodes.index');
    Route::post('/promotions', 'UserDashboardController@promotions_store')->name('promocodes.store');

// ── Marketplace (auth user) ──────────────────────────────────
Route::get('/user/store',              'UserMarketplaceController@explore')->name('user.marketplace.explore');
Route::get('/user/store/create',       'UserMarketplaceController@create')->name('user.marketplace.create');
Route::post('/user/store/store',       'UserMarketplaceController@store')->name('user.marketplace.store');
Route::get('/user/store/my-listings',  'UserMarketplaceController@myListings')->name('user.marketplace.my');
Route::get('/user/store/{id}/edit',    'UserMarketplaceController@edit')->name('user.marketplace.edit');
Route::put('/user/store/{id}/update',  'UserMarketplaceController@update')->name('user.marketplace.update');
Route::delete('/user/store/{id}',      'UserMarketplaceController@destroy')->name('user.marketplace.destroy');
Route::get('/user/store/product/{id}', 'UserMarketplaceController@detail')->name('user.marketplace.detail');
Route::post('/user/store/product/{id}/purchase', 'UserMarketplaceController@purchaseDigitalProduct')->name('user.marketplace.purchase_digital');
Route::post('/user/store/product/{id}/buy-ticket', 'UserMarketplaceController@buyTicket')->name('user.marketplace.buy_ticket');
Route::get('/user/store/product/{id}/download', 'UserMarketplaceController@downloadDigitalProduct')->name('user.marketplace.download_digital');
Route::get('/user/store/my-purchases', 'UserMarketplaceController@myPurchases')->name('user.marketplace.purchases');
Route::get('/user/store/feed',         'UserMarketplaceController@feed')->name('user.marketplace.feed');
Route::get('/dashboard/store', function() {
    return redirect()->route('user.marketplace.explore');
});
Route::get('/user/profile', 'UserDashboardController@profile');

// ── Agent Dashboard (Web) ──────────────────────────────────
Route::group(['prefix' => 'agent'], function () {
    Route::get('/dashboard', 'AgentWebController@index')->name('agent.dashboard');
    Route::get('/scanner', 'AgentWebController@scanner')->name('agent.scanner');
    Route::get('/cash-desk', 'AgentWebController@cashDesk')->name('agent.cashdesk');
    
    // API endpoints for web agent
    Route::post('/scan', 'AgentWebController@processScan')->name('agent.processScan');
    Route::post('/sell', 'AgentWebController@processSale')->name('agent.processSale');
});

});

Route::get('/concert/{any?}', function () {
    return file_get_contents(public_path('concert/index.html'));
})->where('any', '.*');

