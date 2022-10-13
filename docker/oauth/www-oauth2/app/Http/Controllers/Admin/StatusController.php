<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatusController extends Controller
{

    public function index() {
        $sections = [
            'oauth2' => [
                'title' => 'status.oauth2.title',
                'items' => [
                    'status.oauth2.authorize' => route( 'oauth2_authorize' ),
                    'status.oauth2.token' => route( 'oauth2_token' ),
                    'status.oauth2.userinfo' => route( 'oauth2_userinfo' ),
                ],
            ],
            'check_auth' => [
                'title' => 'status.check_auth.title',
                'items' => [
                    'check_auth' => route( 'check_auth' ),
                    'check_auth_js' => route( 'check_auth_js' ),
                ],
            ],
        ];

        return view('admin.status.index')
            ->withSections( $sections );
    }

}
