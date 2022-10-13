<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        sendFailedLoginResponse as protected authenticateUsersSendFailedLoginResponse;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')
            ->except(['logout', 'login_remote']);
    }

    /**
     * Override the AuthenticatesUsers::sendFailedLoginResponse method.
     * Show a message for users that were imported from old rtpa-auth-server v1
     * and invite them to reset their password.
     *
     * @param Request $request
     */
    public function sendFailedLoginResponse(Request $request) {
        $username = $this->username();
        $user = User::where( $username, $request->$username )->first();

        if ($user && $user->password == '') {
            return redirect(url('/password/reset'))
                ->withInput($request->only($this->username(), 'remember'))
                ->withErrors([
                    $this->username() => Lang::get('auth.force_reset_v1'),
                ]);
        }

        // Fallback to standard trait behavior
        return $this->authenticateUsersSendFailedLoginResponse($request);
    }

    /**
     * An authentication hook that will work without CSRF protection.
     * Note that, in order to have this method working, CSRF and Guest
     * middlewares must be disabled.
     *
     * @param Request $request
     */
    public function login_remote(Request $request)
    {
        $login_result = $this->login($request);

        if (Auth::check() && $request->redirect_success) {
            return redirect($request->redirect_success);
        }

        return $login_result;
    }

}
