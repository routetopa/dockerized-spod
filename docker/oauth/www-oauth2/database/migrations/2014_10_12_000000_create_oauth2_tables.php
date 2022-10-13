<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauth2Tables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'oauth_clients', function( Blueprint $table ) {
            $table->string( 'client_id', 80 );
            $table->string( 'client_secret', 80 );
            $table->string( 'redirect_uri', 2000 );
            $table->string( 'grant_types', 80 )->nullable();
            $table->string( 'scope', 100 )->nullable();
            $table->string( 'user_id', 80 )->nullable();
	        $table->string( 'title' );
	        $table->string( 'home_uri', 200 );
	        $table->string( 'init_oauth_uri', 2000 );
	        $table->boolean( 'auto_authorize' )->default( 0 );

            $table->timestamps();
            $table->primary( 'client_id' );
        } );

        Schema::create( 'oauth_access_tokens', function( Blueprint $table ) {
            $table->string( 'access_token', 40);
            $table->string( 'client_id', 80);
            $table->string( 'user_id', 255)->nullable();
            $table->timestamp( 'expires' );
            $table->string( 'scope', 2000)->nullable();

            $table->timestamps();
            $table->primary( 'access_token');
        });

        Schema::create( 'oauth_authorization_codes', function( Blueprint $table ) {
            $table->string( 'authorization_code', 40);
            $table->string( 'client_id', 80);
            $table->string( 'user_id', 255)->nullable();
            $table->string( 'redirect_uri', 2000)->nullable();
            $table->timestamp( 'expires' );
            $table->string( 'scope', 2000)->nullable();
	        // OpenID Connect // $table->string( 'id_token', 1000 )->nullable()->default( null );

            $table->timestamps();
            $table->primary( 'authorization_code');
        });

        Schema::create( 'oauth_refresh_tokens', function( Blueprint $table ) {
            $table->string( 'refresh_token', 40);
            $table->string( 'client_id', 80);
            $table->string( 'user_id', 255)->nullable();
            $table->timestamp( 'expires' );
            $table->string( 'scope', 2000)->nullable();

            $table->timestamps();
            $table->primary( 'refresh_token');
        });

        Schema::create( 'oauth_users', function( Blueprint $table ) {
            $table->increments( 'id' );
            $table->string( 'username', 255 );
            $table->string( 'password' );
            $table->string( 'first_name', 255 )->default( '' );
            $table->string( 'last_name', 255 )->default( '' );
            $table->string( 'email' )->unique();
            $table->string( 'roles' )->default( '' );
            $table->boolean( 'is_banned' )->default(false);
            $table->timestamp( 'last_login_at' )->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create( 'oauth_scopes', function( Blueprint $table ) {
            $table->text( 'scope' );
            $table->boolean( 'is_default' );

            $table->timestamps();
        });

        Schema::create( 'oauth_jwt', function( Blueprint $table ) {
            $table->string( 'client_id', 80 );
            $table->string( 'subject', 80 )->nullable();
            $table->string( 'public_key', 2000 )->nullable();

            $table->timestamps();
            $table->primary( 'client_id' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop( 'oauth_clients' );
        Schema::drop( 'oauth_access_tokens' );
        Schema::drop( 'oauth_authorization_codes' );
        Schema::drop( 'oauth_refresh_tokens' );
        Schema::drop( 'oauth_users' );
        Schema::drop( 'oauth_scopes' );
        Schema::drop( 'oauth_jwt' );
    }
}
