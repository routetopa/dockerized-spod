<?php

namespace App\Http\Controllers;

use App\Client;
use App\Http\Requests;

/**
 * Dashboard for logged-in users.
 *
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware( 'auth' );
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    	$clients = Client::all();

        return view( 'home.index' )
	        ->withClients( $clients );
    }
}
