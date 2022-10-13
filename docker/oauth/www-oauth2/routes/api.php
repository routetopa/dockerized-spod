<?php

use Illuminate\Http\Request;

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

Route::get( 'check_auth', 'Api\AuthCheck@check_cors' )->middleware( 'cors' )->name( 'check_auth' );

Route::get( 'check_auth.js', 'Api\AuthCheck@check_jsonp' )->name( 'check_auth_js' );
