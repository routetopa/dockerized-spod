<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CRUD Controller for OAuth2 Policy entity.
 *
 * @package App\Http\Controllers\Admin
 */
class PolicyController extends Controller
{
	public function __construct()
	{
		$this->middleware( 'admin' );
	}

	public function index()
	{
		$policies = Policy::all();

		return view( 'admin.policy.index' )
			->withPolicies( $policies );
	}

	public function create()
	{
		return view( 'admin.policy.create' )
			->withPolicy( new Policy );
	}

	public function edit( $policy_id, Request $request )
	{
		$policy = Policy::findOrFail( $policy_id );

		return view( 'admin.policy.edit' )
			->withPolicy( $policy );
	}

	public function store( Request $request )
	{
		$policy = new Policy;

		$policy->title = $request->get( 'title' );
		$policy->uri = $request->get( 'uri' );
		$policy->content = $request->get( 'content' );
		$policy->is_mandatory = (boolean) $request->get( 'is_mandatory' );

		$policy->save();

		return redirect( action( 'Admin\PolicyController@index' ) );
	}

	public function update( $policy_id, Request $request )
	{
		$policy = Policy::findOrFail( $policy_id );

		$policy->title = $request->get( 'title' );
		$policy->uri = $request->get( 'uri' );
		$policy->content = $request->get( 'content' );
		$policy->is_mandatory = (boolean) $request->get( 'is_mandatory' );

		$policy->save();

		return redirect( action( 'Admin\PolicyController@index' ) );
	}

	public function destroy( $policy_id )
	{
		$policy = Policy::findOrFail( $policy_id );

		$policy->delete();

		return redirect( action( 'Admin\PolicyController@index' ) );
	}

}