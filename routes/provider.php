<?php

/*
|--------------------------------------------------------------------------
| Provider Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'ProviderController@index')->name('index');
Route::get('/trips', 'ProviderResources\TripController@history')->name('trips');

Route::get('/incoming', 'ProviderController@incoming')->name('incoming');
Route::post('/request/{id}', 'ProviderController@accept')->name('accept');
Route::patch('/request/{id}', 'ProviderController@update')->name('update');
Route::post('/request/{id}/rate', 'ProviderController@rating')->name('rating');
Route::delete('/request/{id}', 'ProviderController@reject')->name('reject');

Route::get('/earnings', 'ProviderController@earnings')->name('earnings');
Route::get('/upcoming', 'ProviderController@upcoming_trips')->name('upcoming');
Route::post('/cancel', 'ProviderController@cancel')->name('cancel');

Route::resource('documents', 'ProviderResources\DocumentController');

Route::get('/profile', 'ProviderResources\ProfileController@show')->name('profile.index');
Route::post('/profile', 'ProviderResources\ProfileController@store')->name('profile.update');

Route::get('/location', 'ProviderController@location_edit')->name('location.index');
Route::post('/location', 'ProviderController@location_update')->name('location.update');

Route::get('/profile/password', 'ProviderController@change_password')->name('change.password');
Route::post('/change/password', 'ProviderController@update_password')->name('password.update');

Route::post('/profile/available', 'ProviderController@available')->name('available');
// HEATMAP PROVIDER
Route::get('/heatmap/current', 'Provider\HeatmapController@current')->name('heatmap.current');

// PROVIDER STORE (Marketplace listings management)
Route::get('/store', 'ProviderStoreController@index')->name('store.index');
Route::get('/store/create', 'ProviderStoreController@create')->name('store.create');
Route::post('/store', 'ProviderStoreController@store')->name('store.store');
Route::get('/store/{id}/edit', 'ProviderStoreController@edit')->name('store.edit');
Route::put('/store/{id}', 'ProviderStoreController@update')->name('store.update');
Route::delete('/store/{id}', 'ProviderStoreController@destroy')->name('store.destroy');

Route::get('/wallet', 'ProviderController@wallet')->name('wallet');
Route::get('/governance', 'ProviderController@governance')->name('governance');
Route::get('/support', 'ProviderController@support')->name('support');

// Notifications
Route::get('/notifications', 'ProviderResources\NotificationController@index')->name('notifications');
Route::post('/notifications/{id}/read', 'ProviderResources\NotificationController@markRead')->name('notifications.read');
Route::post('/notifications/read-all', 'ProviderResources\NotificationController@markAllRead')->name('notifications.read-all');
Route::get('/notifications/unread-count', 'ProviderResources\NotificationController@unreadCount')->name('notifications.unread-count');



