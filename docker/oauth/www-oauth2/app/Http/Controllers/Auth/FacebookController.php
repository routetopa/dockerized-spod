<?php

namespace App\Http\Controllers\Auth;

use App\FacebookLogin;
use App\Http\Controllers\Controller;
use App\Setting;
use App\User;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacebookController extends Controller
{

    protected function getFB() {
        return new \Facebook\Facebook([
            'app_id' => Setting::retrieve('fb_app_id'),
            'app_secret' => Setting::retrieve('fb_app_secret'),
            'default_graph_version' => 'v2.10',
        ]);
    }

    public function login()
    {
        $fb = $this->getFB();

        $helper = $fb->getRedirectLoginHelper();
        $permissions = [
            'email',
            'public_profile',
        ];
        $callback_url = action('Auth\FacebookController@callback');

        $loginUrl = $helper->getLoginUrl($callback_url, $permissions);

        return redirect($loginUrl);
    }

    public function callback(Request $request)
    {
        $fb = $this->getFB();

        $helper = $fb->getRedirectLoginHelper();
        $helper->getPersistentDataHandler()->set('state', $request->get('state'));

        try {
            $accessToken = $helper->getAccessToken();
        } catch (FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (!isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }

        // Logged in
        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);

        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateAppId(Setting::retrieve('fb_app_id'));

        // If you know the user ID this access token belongs to, you can validate it here
        //$tokenMetadata->validateUserId('123');
        $tokenMetadata->validateExpiration();

        if (!$accessToken->isLongLived()) {
            // Exchanges a short-lived access token for a long-lived one
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (FacebookSDKException $e) {
                echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
                exit;
            }
        }

        // User is logged in with a long-lived access token.
        session('fb_access_token', $accessToken);

        // Retrieve name and email
        $fb_user = null;
        try {
            $response = $fb->get('/me?fields=id,name,email,first_name,last_name', $accessToken);
            $fb_user = $response->getGraphUser();
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        // Retrieve data from Facebook
        $fb_id = $fb_user->getId();
        $fb_mail = $fb_user->getEmail();
        $fb_first_name = $fb_user->getFirstName();
        $fb_last_name = $fb_user->getLastName();

        // Check if user exists
        $user = null;
        $facebook_login = FacebookLogin::where('fb_mail', $fb_mail)->first();

        if ($facebook_login) {
            // The user previously logged in using Facebook
            $user = $facebook_login->user;
        } else {
            // Check if user is already registered
            $user = User::where('email', $fb_mail)->first();

            if ( ! $user ) {
                // Email not present, create a user
                $user = new User;
                $user->first_name = $fb_first_name;
                $user->last_name = $fb_last_name;
                $user->email = $fb_mail;
                $user->password = '';
                $user->roles = '';
                $user->is_banned = false;
                $user->verified = false;
                $user->save();
            }

            // Associate Facebook login information to local user
            $facebook_login = new FacebookLogin;
            $facebook_login->fb_id = $fb_id;
            $facebook_login->fb_mail = $fb_mail;
            $user->facebook()->save($facebook_login);
        }

        // Login
        Auth::login($user, true);

        // You can redirect them to a members-only page.
        return redirect()->intended();
    }

}
