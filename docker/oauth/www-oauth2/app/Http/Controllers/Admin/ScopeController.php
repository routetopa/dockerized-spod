<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Scope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CRUD Controller for OAuth2 Scope entity.
 *
 * @package App\Http\Controllers\Admin
 */
class ScopeController extends Controller
{
    public function __construct()
    {
        $this->middleware( 'admin' );
    }

    public function index()
    {
        $scopes = Scope::all();

        return view( 'admin.scope.index' )
            ->withScopes( $scopes );
    }

    public function create()
    {
        return view( 'admin.scope.create' )
            ->withScope( new Scope );
    }

    public function edit( $scope_id, Request $request )
    {
        $scope = Scope::findOrFail( $scope_id );

        return view( 'admin.scope.edit' )
            ->withScope( $scope );
    }

    public function store( Request $request )
    {
        $scope = new Scope;

        $scope->scope = $request->get( 'scope' );
        $scope->is_default = (boolean) $request->get( 'is_default' );

        $scope->save();

        $this->consolidateAfterUpdate( $scope );

        return redirect( action( 'Admin\ScopeController@index' ) );
    }

    public function update( $scope_id, Request $request )
    {
        $scope = Scope::findOrFail( $scope_id );

        $scope->scope = $request->get( 'scope' );
        $scope->is_default = (boolean) $request->get( 'is_default' );

        $scope->save();

        $this->consolidateAfterUpdate( $scope );

        return redirect( action( 'Admin\ScopeController@index' ) );
    }

    public function destroy( $scope_id )
    {
        $scope = Scope::findOrFail( $scope_id );

        $scope->delete();

        $this->consolidateAfterDelete( $scope );

        return redirect( action( 'Admin\ScopeController@index' ) );
    }

    protected function consolidateAfterUpdate( Scope $scope )
    {
        if ( $scope->is_default )
        {
            DB::table( $scope->getTable() )
                ->where( 'scope', '<>', $scope->scope )
                ->update( [ 'is_default' => 0 ] );
        }
    }

    protected function consolidateAfterDelete( Scope $scope )
    {
        if ( $scope->is_default )
        {
            DB::table( $scope->getTable() )
                ->limit( 1 )
                ->update( [ 'is_default' => 1] );
        }
    }

}