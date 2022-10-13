<?php

namespace App\Http\Controllers;

use App\Client;
use App\Scope;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use OAuth2;

/**
 * Manages OAuth2 and OpenIDConnect flows.
 *
 * @package App\Http\Controllers
 */
class OAuth2Controller extends Controller
{

	function auth( Request $request )
	{
		$request  = OAuth2\HttpFoundationBridge\Request::createFromRequest( $request );
		$response = new OAuth2\HttpFoundationBridge\Response();

		$server = App::make( 'oauth2' );

		$server->setScopeUtil( new OAuth2\Scope( [
			'supported_scopes' => Scope::all( 'scope' )->pluck( 'scope' )->toArray(),
		] ) );

		// validate the authorize request
		if ( ! $server->validateAuthorizeRequest( $request, $response ) )
		{
			return $response;
		}

		$oauthClient = Client::find( $request->query( 'client_id', null ) );

		// display an authorization form
		// skip authorization if requesting client is set on auto_authorize
		if ( empty( $_POST ) && ! $oauthClient->auto_authorize )
		{
			$scope = Scope::find( $request->query( 'scope', null ) );

			return view( 'oauth2.authorize' )
				->withClient( $oauthClient )
				->withScope( $scope )
				->withUser( Auth::user() );
		}

		// print the authorization code if the user has authorized your client
		$is_authorized = ( $request->get( 'authorized' ) === 'yes' ) || $oauthClient->auto_authorize;
		$server->handleAuthorizeRequest( $request, $response, $is_authorized, Auth::user()->username );

		return $response;
	}

	function token( Request $request )
	{
	    $server = App::make('oauth2');
		$server->addGrantType(
			new OAuth2\GrantType\AuthorizationCode( new OAuth2\Storage\Pdo( App::make( 'db' )->getPdo() ) )
		);

		$bridgedRequest  = OAuth2\HttpFoundationBridge\Request::createFromRequest( $request );
		$bridgedResponse = new OAuth2\HttpFoundationBridge\Response();

		$bridgedResponse = $server->handleTokenRequest( $bridgedRequest, $bridgedResponse );

		return $bridgedResponse;
	}

	private function decodeJwt( Request $request ) {
	    $header = $request->header( "Authorization" );
        $jwt_access_token = substr( $header, 7 ); // Remove "Bearer " string

        $separator = '.';

        if (2 !== substr_count($jwt_access_token, $separator)) {
            //throw new \Exception("Incorrect access token format");
            return json_encode( [ "error" => "Incorrect access token format" ] );
        }

        list($header, $payload, $signature) = explode($separator, $jwt_access_token);

        $decoded_signature = base64_decode(str_replace(array('-', '_'), array('+', '/'), $signature));

        // The header and payload are signed together
        $payload_to_verify = utf8_decode($header . $separator . $payload);

        // however you want to load your public key
        $public_key = file_get_contents( setting( 'key_public' ) );

        // default is SHA256
        $verified = openssl_verify($payload_to_verify, $decoded_signature, $public_key, OPENSSL_ALGO_SHA256);

        if ($verified !== 1) {
            //throw new Exception("Cannot verify signature");
            return json_encode( [ "error" => "Cannot verify signature" ] );
        }

        return base64_decode($payload);
    }

	function userinfo( Request $request )
	{
		$server = App::make( 'oauth2' );
		$user_id = null;

		if ( setting('jwt_enable' ) ) {
		    $payload = json_decode( $this->decodeJwt( $request ) );

		    if ( property_exists( $payload, 'error' ) ) {
                return Response::json( $payload );
            }

		    $user_id = $payload->sub;
        } else {
            // Do NOT use ::createFromRequest as it does not distinguish between query and post params
            $bridgedRequest = OAuth2\HttpFoundationBridge\Request::createFromGlobals();
            $bridgedResponse = new OAuth2\HttpFoundationBridge\Response();

            if ( $server->verifyResourceRequest( $bridgedRequest, $bridgedResponse ) )
            {
                $token = $server->getAccessTokenData( $bridgedRequest );

                $user_id = $token[ 'user_id' ];
            }
            else
            {
                return Response::json( [ 'error' => 'Unauthorized' ], $bridgedResponse->getStatusCode() );
            }
        }

        $user = User::whereUsername( $user_id )->first();

        return Response::json( [
            //'private' => 'stuff',
            'email' => $user->email,
            'user_id' => $user_id,
            //'client'  => $token[ 'client_id' ],
            //'expires' => $token[ 'expires' ],
        ] );

	}

}