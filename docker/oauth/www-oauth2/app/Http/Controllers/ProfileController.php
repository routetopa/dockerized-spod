<?php

namespace App\Http\Controllers;

use App\Http\Requests;

/**
 * Allows users to edit their own profile.
 *
 * @package App\Http\Controllers
 */
class ProfileController extends Controller
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
    public function edit()
    {
        return view( 'profile.edit' );
    }
}
