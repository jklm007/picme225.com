<?php

/*
|--------------------------------------------------------------------------
| Marketing Routes (ISOLATED)
|--------------------------------------------------------------------------
|
| These routes are for SEO landing pages and Google Ads funnels.
| They are 100% independent from the core app routes.
| No authentication required.
|
*/

// Landing Pages
Route::get('/airport', 'Marketing\MarketingController@airport')->name('marketing.airport');

// SEO Blog Pages
Route::get('/blog', 'Marketing\BlogController@index')->name('marketing.blog.index');
Route::get('/blog/{slug}', 'Marketing\BlogController@show')->name('marketing.blog.show');

// SEO
Route::get('/sitemap.xml', 'Marketing\MarketingController@sitemap')->name('marketing.sitemap');
