<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class Locale
{
    /**
     * Sets locale.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
	    $locale = $request->session()->get( 'locale' );
		if ( $locale )
		{
			App::setLocale( $locale );
		}

        return $next( $request );
    }
}
