<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * CRUD Controller for Setting entity.
 *
 * @package App\Http\Controllers\Admin
 */
class SettingController extends Controller
{

    public function __construct()
    {
        $this->middleware( 'admin' );
    }

    public function edit()
    {
        $settings = Setting::all()->keyBy( 'key' );

        return view( 'admin.settings.edit' )
            ->withSettings( $settings );
    }

    public function update( Request $request )
    {

        $settings = $request->all();

        foreach ( $settings as $k => $v )
        {
            if ( 'settings_' == substr( $k, 0, 9 ) )
            {
                $key = substr( $k, 9 );
                $setting = Setting::find( $key );
                if ( ! $setting )
                {
                    // TODO Setting not found
                }
                else if ( $setting->value != $v )
                {
                    $setting->value = $v;
                    $setting->save();
                }
            }
        }

        return redirect()->action( 'Admin\SettingController@edit' );

    }

    public function keys()
    {
        return view( 'admin.settings.keys' );
    }

    public function keys_generate( Request $request )
    {
        // Generate a keys pair with default values
        $res = openssl_pkey_new();

        if ( ! $res )
        {
            return redirect()->action( 'Admin\SettingController@edit' );
        }

        // Extract private key
        openssl_pkey_export( $res, $private_key );

        // Extract public key
        $public_key = openssl_pkey_get_details( $res );
        $public_key = $public_key[ "key" ];

        // use current time in filename
        $now = date( 'YmdHi' );

        // Save keys to disk
        $public_name = "keys_{$now}_pub.pem";
        $private_name = "keys_{$now}_priv.pem";
        Storage::put( $public_name, $public_key );
        Storage::put( $private_name, $private_key );

        // Save keys paths to Settings
        $public_setting = Setting::find( 'key_public' );
        $public_setting->value =  storage_path( "app/$public_name" );
        $public_setting->save();

        $private_setting = Setting::find( 'key_private' );
        $private_setting->value = storage_path( "app/$private_name" );
        $private_setting->save();

        // Redirect to settings
        return redirect()->action( 'Admin\SettingController@edit' );
    }

}
