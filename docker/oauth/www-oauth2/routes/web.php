<?php

Route::post( 'lang/{lang?}', function( $lang = null ) {
	session( [ 'locale' => $lang ] );
	$uri = \Illuminate\Support\Facades\Request::input( 'backuri' ) ?: '/';
	return redirect( $uri );
} );

/*
|------------------------------------------------------------------------------
| OAuth routes
|------------------------------------------------------------------------------
|
| These routes are hooked to the OAuth2Controller which manages
| OAuth2 / OpenIDConnect flows.
|
*/
Route::group( [ 'prefix' => 'oauth', 'middleware' => [ /*'auth', 'verified'*/ ] ], function() {
    Route::any( 'authorize', 'OAuth2Controller@auth' )->middleware( 'auth' )->name( 'oauth2_authorize' );
    Route::post( 'token', 'OAuth2Controller@token' )->name( 'oauth2_token' );
    Route::get( 'v1/userinfo', 'OAuth2Controller@userinfo' )->name( 'oauth2_userinfo' );
} );

/*
|------------------------------------------------------------------------------
| Facebook routes
|------------------------------------------------------------------------------
|
| Enables Login/Logout via Facebook
|
*/
Route::group( [ 'prefix' => 'fb' ], function() {
    Route::any( 'login', 'Auth\FacebookController@login' );
    Route::any( 'callback', 'Auth\FacebookController@callback' );
} );

/*
|------------------------------------------------------------------------------
| Authentication routes
|------------------------------------------------------------------------------
|
| These routes allows logg in & out from the authentication server,
| new user registration and password reset.
|
*/
Route::post( 'login/remote', 'Auth\LoginController@login_remote' );
Route::get ( 'register/sent', 'Auth\RegisterController@showValidationSent' );
Route::post( 'register/send', 'Auth\RegisterController@sendValidation' );
Route::auth();

/*
|------------------------------------------------------------------------------
| Logged users routes
|------------------------------------------------------------------------------
|
| These routes are only accessible to logged-in users.
|
*/
Route::group( [ 'middleware' => [ 'auth', 'verified' ] ], function() {
    Route::get( 'logout', 'Auth\LogoutController@logoutConfirm' );
    Route::get( 'profile', 'ProfileController@edit' );
	Route::get( '/', 'HomeController@index' )->name( 'home' );
} );

/*
|------------------------------------------------------------------------------
| Admin routes
|------------------------------------------------------------------------------
|
| These routes are only accessible to Administrators.
|
*/
Route::group( [ 'prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => 'admin' ], function() {
    Route::get( 'setting/keys', 'SettingController@keys' );
    Route::post( 'setting/keys', 'SettingController@keys_generate' );
    Route::get( 'setting', 'SettingController@edit' );
    Route::put( 'setting', 'SettingController@update' );

    Route::get( 'status', 'StatusController@index' );

    Route::any( 'user/{user}/ban', 'UserController@ban' );
    Route::resource( 'user', 'UserController' );

	Route::resource( 'policy', 'PolicyController' );

    Route::resource( 'oauth2-client', 'ClientController' );
    Route::resource( 'oauth2-scope', 'ScopeController' );

    Route::get( 'import/users', 'ImportController@importUsers' );
    Route::post( 'import/users', 'ImportController@importUsersDo' );
} );
