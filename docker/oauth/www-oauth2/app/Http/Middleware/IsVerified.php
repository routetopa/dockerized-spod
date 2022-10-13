<?php

namespace App\Http\Middleware;

use Closure;

class IsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        $email_verified = $user->verified;
        $facebook_login = $user->facebook;
	    if( ! ($email_verified || $facebook_login) ){
		    return redirect()->action( 'Auth\RegisterController@showValidationSent' );
	    }

        return $next( $request );
    }
}
