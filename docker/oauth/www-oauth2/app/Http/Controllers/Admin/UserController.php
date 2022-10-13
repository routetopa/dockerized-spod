<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * CRUD Controller for User entity.
 *
 * @package App\Http\Controllers\Admin
 */
class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware( 'admin' );
    }

    public function index()
    {
        $users = User::all();

        return view( 'admin.user.index' )
            ->withUsers( $users );
    }

    public function create()
    {
        return view( 'admin.user.create' )
            ->withUser( new User );
    }

    public function edit( User $user )
    {
        return view( 'admin.user.edit' )
            ->withUser( $user );
    }

    public function store( Request $request )
    {
        if ( $request->get( 'password' ) !== $request->get( 'password_confirm' ) )
        {
            // TODO Launch error
        }

        $user = new User;
        $user->email = $request->get( 'email' );
        $user->is_banned = $request->get( 'is_banned' );
        $user->password = bcrypt( $request->get( 'password' ) );

        if ( $request->get( 'is_admin' ) )
        {
            $user->roles = 'admin';
        }

        $user->save();

        return redirect( action( 'Admin\UserController@index' ) );
    }

    public function update( User $user, Request $request )
    {
        if ( $request->has( 'is_banned' ) && $request->get( 'is_banned' ) && $user->id == Auth::user()->id )
        {
            // TODO Throw proper Exception
            die( 'Operation not allowed. Can\'t ban yourself.' );
        }

        if ( $request->get( 'password' ) && $request->get( 'password' ) === $request->get( 'password_confirm' ) )
        {
            $user->password = bcrypt( $request->get( 'password' ) );
        }

        $user->is_banned = $request->get( 'is_banned' );
        $user->roles = $request->get( 'is_admin' ) ? 'admin' : '';

        $user->save();

        return redirect( action( 'Admin\UserController@index' ) );
    }

    public function destroy( User $user )
    {
        if ( $user->id == Auth::user()->id )
        {
            // TODO Throw proper Exception
            die( 'Operation not allowed. Can\'t remove yourself.' );
        }

        if ( User::count() == 1 )
        {
            // TODO Throw proper Exception
            die( 'Operation not allowed. Can\'t remove the last user.' );
        }

        $user->delete();

        return redirect( action( 'Admin\UserController@index' ) );
    }

    public function ban( User $user )
    {
        if ( $user->id == Auth::user()->id )
        {
            // TODO Throw proper Exception
            die( 'Operation not allowed. Can\'t ban yourself.' );
        }

        $user->is_banned = true;
        $user->save();

        return redirect( action( 'Admin\UserController@index' ) );
    }

}
