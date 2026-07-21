$content = Get-Content -Raw routes\web.php
$search = "Route::get\('/', 'HomeController@index'\);\r?\nRoute::get\('/marketplace', 'HomeController@marketplace'\)->name\('marketplace.public'\);\r?\nRoute::get\('/location', 'RentalController@index'\)->name\('rental.location.index'\);\r?\nRoute::get\('/estimate/fare', 'HomeController@estimate_fare'\);"
$replace = "Route::group(['middleware' => 'language'], function () {`r`n    Route::get('/', 'HomeController@index');`r`n    Route::get('/marketplace', 'HomeController@marketplace')->name('marketplace.public');`r`n    Route::get('/location', 'RentalController@index')->name('rental.location.index');`r`n    Route::get('/estimate/fare', 'HomeController@estimate_fare');`r`n});"
$newContent = [regex]::Replace($content, $search, $replace)
Set-Content -Path routes\web.php -Value $newContent -NoNewline
