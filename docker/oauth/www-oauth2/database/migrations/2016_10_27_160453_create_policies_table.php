<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create( 'policies', function ( Blueprint $table ) {
		    $table->increments( 'id' );
		    $table->string( 'title' );
		    $table->string( 'uri' );
		    $table->text( 'content' );
		    $table->boolean( 'is_mandatory' );
		    $table->timestamps();
	    } );

	    Schema::create( 'policy_user', function ( Blueprint $table ) {
			$table->integer( 'policy_id' );
		    $table->integer( 'user_id' );
		    $table->boolean( 'accepted' );
		    $table->timestamps();

		    $table->primary( [ 'policy_id', 'user_id' ] );
	    } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::drop( 'policies' );
	    Schema::drop( 'policy_user' );
    }
}
