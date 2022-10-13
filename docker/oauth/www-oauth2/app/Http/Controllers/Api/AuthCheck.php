<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class AuthCheck extends Controller
{
	/**
	 * Generates a JSON response that is used as response for CORS or JSONP requests.
	 *
	 * @return array
	 */
	protected function generateJsonResponse()
	{
		return [
			'is_authenticated' => Auth::check() ? 1 : 0,
		];
	}

	/**
	 * Check if &subject is a valid JavaScript identifier.
	 *
	 * @see http://www.geekality.net/2011/08/03/valid-javascript-identifier/
	 *
	 * @param String $subject
	 *
	 * @return bool
	 */
	protected function isValidIdentifier( $subject )
	{
		$identifier_syntax =
			'/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';

		$reserved_words = array('break', 'do', 'instanceof', 'typeof', 'case',
			'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue',
			'for', 'switch', 'while', 'debugger', 'function', 'this', 'with',
			'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum',
			'extends', 'super', 'const', 'export', 'import', 'implements', 'let',
			'private', 'public', 'yield', 'interface', 'package', 'protected',
			'static', 'null', 'true', 'false');

	    return preg_match( $identifier_syntax, $subject )
	           && ! in_array( mb_strtolower( $subject, 'UTF-8' ), $reserved_words );
	}

	/**
	 * Answers a CORS request.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
    public function check_cors()
    {
	    if ( ! setting( 'api_check_cors_enable' ) )
	    {
		    App::abort( 404 );
	    }

	    return response()->json( $this->generateJsonResponse() );
    }

	/**
	 * Answers a JSONP request.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
    public function check_jsonp( Request $request )
    {
	    if ( ! setting( 'api_check_jsonp_enable' ) )
	    {
		    App::abort( 404 );
	    }

	    $callback = $request->get( 'onload' );

	    if ( ! $callback )
	    {
		    App::abort( 400, 'Missing onload parameter' );
	    }

	    if ( ! $this->isValidIdentifier( $callback ) || strlen( $callback ) > 512 )
	    {
		    App::abort( 400, 'Invalid callback name' );
	    }

	    return $callback . '(' . json_encode( $this->generateJsonResponse() ) . ');';
    }
}
