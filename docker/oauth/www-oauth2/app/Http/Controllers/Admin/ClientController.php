<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Client;
use Illuminate\Http\Request;

/**
 * CRUD Controller for OAuth2 Client entity.
 *
 * @package App\Http\Controllers\Admin
 */
class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware( 'admin' );
    }

    public function index()
    {
        $clients = Client::all();

        return view( 'admin.client.index' )
            ->withClients( $clients );
    }

    public function create()
    {
        return view( 'admin.client.create' )
            ->withClient( new Client );
    }

    public function edit( $client_id )
    {
	    $client = Client::findOrFail( $client_id );

        return view( 'admin.client.edit' )
            ->withClient( $client );
    }

    public function store( Request $request )
    {
        $client = new Client;

        $client->client_id = $request->get( 'client_id' );
	    $client->client_secret = $request->get( 'client_secret' );
	    $client->redirect_uri = $request->get( 'redirect_uri' );
	    $client->grant_types = $request->get( 'grant_types' );
	    $client->scope = $request->get( 'scope' );
	    $client->user_id = $request->get( 'user_id' );
	    $client->title = $request->get( 'title' );
	    $client->home_uri = $request->get( 'home_uri' );
	    $client->init_oauth_uri = $request->get( 'init_oauth_uri' );
	    $client->auto_authorize = $request->get( 'auto_authorize' );

        $client->save();

        return redirect( action( 'Admin\ClientController@index' ) );
    }

    public function update( $client_id, Request $request )
    {
	    $client = Client::findOrFail( $client_id );

	    $client->client_id = $request->get( 'client_id' );
	    $client->client_secret = $request->get( 'client_secret' );
	    $client->redirect_uri = $request->get( 'redirect_uri' );
	    $client->grant_types = $request->get( 'grant_types' );
	    $client->scope = $request->get( 'scope' );
	    $client->user_id = $request->get( 'user_id' );
	    $client->title = $request->get( 'title' );
	    $client->home_uri = $request->get( 'home_uri' );
	    $client->init_oauth_uri = $request->get( 'init_oauth_uri' );
	    $client->auto_authorize = $request->get( 'auto_authorize' );

        $client->save();

        return redirect( action( 'Admin\ClientController@index' ) );
    }

    public function destroy( $client_id )
    {
	    $client = Client::findOrFail( $client_id );

        $client->delete();

        return redirect( action( 'Admin\ClientController@index' ) );
    }

}