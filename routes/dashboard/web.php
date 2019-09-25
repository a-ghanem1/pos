<?php

Route::prefix('dashboard')->name('dashboard.')->group(function() {
	
	Route::get('/index', 'DashboardController@index')->name('index');
});