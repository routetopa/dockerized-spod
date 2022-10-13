<?php

namespace App\Http\Controllers\Auth;

use App\Notifications\EmailVerification;
use App\Notifications\UserVerificationNotification;
use App\Policy;
use App\User;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;
use Validator;

use Jrean\UserVerification\Traits\VerifiesUsers;
use Jrean\UserVerification\Facades\UserVerification;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers {
	    showRegistrationForm as traitShowRegistrationForm;
    }

    use VerifiesUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

	/**
	 * Name of the view returned by the getVerificationError method.
	 *
	 * @var string
	 */
	protected $verificationErrorView = 'auth.register.error';

	/**
	 * name of the users table.
	 *
	 * @var string
	 */
	protected $userTable = 'oauth_users';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
	    // Normally, this controller is accessible only by guest user, but we let
	    // actions 'showValidationSent' and 'sendValidation' accessible to registered
	    // but not verified users
        $this->middleware( 'guest' )->except(
	        [ 'showValidationSent', 'sendValidation' ]
        );

	    $this->middleware( function( $request, $next ) {
		    if ( Auth::user()->verified )
		    {
			    return redirect( $this->redirectIfVerified() );
		    }

		    return $next($request);
	    } )->only(
		    [ 'showValidationSent', 'sendValidation' ]
	    );
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @param  array  $additionalRules
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator( array $data, array $additionalRules = [], $customMessages = [] )
    {
	    $rules = [
		    'email' => 'required|email|max:255|unique:oauth_users',
		    'password' => 'required|min:6|confirmed',
	    ];
	    $rules = array_merge( $rules, $additionalRules );
        return Validator::make( $data, $rules, $customMessages );
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

	/**
	 * Show the application registration form.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function showRegistrationForm()
	{
		if ( setting( 'registration_open' ) )
		{
			$policies = Policy::all();

			return $this->traitShowRegistrationForm()
				->withPolicies( $policies );
		}
		else
		{
			return view( 'auth.register_closed' );
		}
	}

	/**
	 * Handle a registration request for the application.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function register(Request $request)
	{
		// Create policies rules and messages
		$policies = Policy::all();
		$additionalRules = [];
		$policiesMessages = [];
		foreach ( $policies as $p )
		{
			if ( $p->is_mandatory )
			{
				$additionalRules[ "policy_{$p->id}" ] = 'accepted';
				$policiesMessages[ "policy_{$p->id}.accepted" ] =
					trans( 'validation.accepted', [ 'attribute' => $p->title ] );
			}
		}

		// Create a validator for user input and policies
		$this->validator( $request->all(), $additionalRules, $policiesMessages )->validate();

		// Validator passed: create user
		$user = $this->create( $request->all() );
		$this->guard()->login( $user );

		// Add policies to user profile
		foreach ( $policies as $p )
		{
			$user->policies()->attach( $p->id, [
				'accepted' => $request->get( "policy_{$p->id}" ),
				'created_at' => Carbon::now(),
			] );
		}

		// Show confirmation message
		return $this->sendValidation();
	}

	/**
	 * Send a new verification email.
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function sendValidation()
	{
		$user = Auth::user();

		UserVerification::generate( $user );

		$user->notify( new EmailVerification() );

		return redirect()->action( 'Auth\RegisterController@showValidationSent' );
	}

	/**
	 * Show a message about the verification email, and allow to send a new verification email.
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function showValidationSent()
	{
		return view( 'auth.register_sent' );
	}
}
