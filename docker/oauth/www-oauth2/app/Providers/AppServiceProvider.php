<?php

namespace App\Providers;

use App\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use LucaVicidomini\BladeMaterialize\BladeExtender;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        BladeExtender::extend();

	    Setting::saved( function( $setting ) {
		    $setting->purge();
	    } );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
