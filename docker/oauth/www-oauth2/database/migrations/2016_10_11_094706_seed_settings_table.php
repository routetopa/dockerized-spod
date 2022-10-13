<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table( 'settings' )->insert( [
            [ 'key' => 'registration_open', 'value' => '0' ],
	        [ 'key' => 'instance_title', 'value' => 'ROUTE-TO-PA' ],
	        [ 'key' => 'api_check_jsonp_enable', 'value' => '1' ],
	        [ 'key' => 'api_check_cors_enable', 'value' => '1' ],
            [ 'key' => 'fb_app_id', 'value' => '' ],
            [ 'key' => 'fb_app_secret', 'value' => '' ],
            [ 'key' => 'jwt_enable', 'value' => '0' ],
            [ 'key' => 'key_private', 'value' => '' ],
            [ 'key' => 'key_public', 'value' => '' ],
        ] );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
