<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class ImportController extends Controller
{

    public function importUsers( Request $request )
    {
        $users = $request->session()->has( 'users' ) ? $request->session()->get( 'users' ) : [];
        sort( $users );

        return view( 'admin.import.users' )
            ->withUsers( $users );
    }

    public function importUsersDo( Request $request )
    {
        $users = [];
        $errors = new MessageBag();
        $lineNumber = 0;
        $file = null;
        $verified_only = $request->option_only_verified;
        
        if ( ! $request->csv->isValid() ) {
            $errors->add( 'csv', trans( 'import.users.errors.file_upload' ) );

            return redirect()->action( 'Admin\ImportController@importUsers' )
                ->withUsers( $users )
                ->withErrors( $errors );
        }
        
        try
        {
            $file = $request->csv->openFile();
            $file->setFlags( \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE ); // Skip empty lines
        } catch ( \RuntimeException $e ) {
            $errors->add( 'system', trans( 'import.users.errors.file_open' ) );

            return redirect()->action( 'Admin\ImportController@importUsers' )
                ->withUsers( $users )
                ->withErrors( $errors );
        }

        while ( ! $file->eof() )
        {
            $record = $file->fgetcsv();
            $lineNumber++;

            // fgetcsv error
            if ( FALSE === $record )
            {
                $errors->add( $lineNumber, trans( 'import.users.errors.line_not_csv', [ 'line' => $lineNumber ] ) );
                continue;
            }

            // Empty line (skip without error)
            if ( count( $record ) == 0 )
            {
                continue;
            }

            // Invalid field count
            $expectedValuesCnt = 3;
            if ( count( $record ) != $expectedValuesCnt )
            {
                $errors->add( $lineNumber, trans( 'import.users.errors.line_wrong_count', [ 'line' => $lineNumber, 'values' =>  count( $record ), 'expected' => $expectedValuesCnt ] ) );
                continue;
            }

            list( $email, $is_admin, $is_verified ) = $record;

            // Invalid e-mail
            if ( ! filter_var( $email , FILTER_VALIDATE_EMAIL ) )
            {
                $errors->add( $lineNumber, trans( 'import.users.errors.line_invalid_email', [ 'line' => $lineNumber, 'email' => $email ] ) );
                continue;
            }

            // is_admin is not a valid boolean
            if ( NULL === filter_var( $is_admin, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) )
            {
                $errors->add( $lineNumber, trans( 'import.users.errors.line_invalid_is_admin', [ 'line' => $lineNumber, 'is_admin' => $is_admin ] ) );
                continue;
            }

            // is_verified is not a valid boolean
            if ( NULL === filter_var( $is_verified, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) )
            {
                $errors->add( $lineNumber, trans( 'import.users.errors.line_invalid_is_verified', [ 'line' => $lineNumber, 'is_verified' => $is_verified ] ) );
                continue;
            }

            // Everything ok, create the user
            $user = new User();
            $user->email = $email;
            $user->is_banned = 0;
            $user->roles = $is_admin ? 'admin' : '';
            $user->verified = $is_verified;
            $user->password = ''; // Force user to reset his password
$user->password = bcrypt( explode('@', $email)[0] );

            if ( $verified_only && ! $user->verified )
            {
                $errors->add( $user->email, trans( 'import.users.errors.not_verified', [ 'email' => $user->email ] ) );
                continue;
            }

            if ( User::where( 'email', $user->email )->first() )
            {
                $errors->add( $user->email, trans( 'import.users.errors.already_present', [ 'email' => $user->email ] ) );
                continue;
            }

            $result = $user->save();
            if ( $result )
            {
                $users[] = $user->email;
            } else {
                $errors->add( $user->email, trans( 'import.users.errors.save', [ 'email' => $user->email ] ) );
            }
        }

        return redirect()->action( 'Admin\ImportController@importUsers' )
            ->withUsers( $users )
            ->withErrors( $errors );
    }

}
