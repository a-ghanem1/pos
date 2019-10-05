<?php

Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => [ 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath' ]], function() {
		Route::prefix('dashboard')->name('dashboard.')->middleware(['auth'])->group(function() {
		
			//index route
			Route::get('/', 'WelcomeController@index')->name('welcome');
		
			// user routes
			Route::resource('users', 'UserController')->except(['show']);

			// category routes
			Route::resource('categories', 'CategoryController')->except(['show']);

			// product routes
			Route::resource('products', 'ProductController')->except(['show']);

			// client routes
			Route::resource('clients', 'ClientController')->except(['show']);
			Route::resource('clients.orders', 'Client\OrderController')->except('show');
		});	
	});

