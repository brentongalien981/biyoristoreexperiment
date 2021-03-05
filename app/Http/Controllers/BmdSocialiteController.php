<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use App\BmdAuth;
use App\AuthProviderType;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;

class BmdSocialiteController extends Controller
{
    // TODO:ON-DEPLOYMENT: Edit this.
    private const APP_FRONTEND_SIGNUP_RESULT_URL = 'https://bmd.com/bmd-socialite-signup-result';



    public function testsignupWithAuthProvider(Request $r)
    {
        // TODO:ON-ITERATION-003: Use a bmd-signup-hash-code for this workflow to allow only legit signups.
        // Maybe use session->put() for this.


        return $this->testhandleProviderCallback($r);
    }



    public function signupWithAuthProvider(Request $r)
    {
        // TODO:ON-ITERATION-003: Use a bmd-signup-hash-code for this workflow to allow only legit signups.
        // Maybe use session->put() for this.


        $provider = $r->provider;

        switch ($provider) {
            case 'google':
            case 'facebook':
                return Socialite::driver($provider)->redirect();
                break;
            default:
                return 'Invalid request.';
                break;
        }
    }



    public function testhandleProviderCallback(Request $r)
    {
        try {
            /** 1) TODO: Make sure the dynamodb session/cache driver is configured. */

            // TODO:ON-ITERATION-003: Use a bmd-signup-hash-code for this workflow to allow only legit signups.
            // Maybe use session->put() for this.



            $socialiteUser = Socialite::driver('google')->user();


            /** 2) Create a reference-only-type user-obj (not really signed-up user using Laravel app). */
            $uObj = new User();
            $uObj->email = $socialiteUser->email;
            $uObj->password = Hash::make(Str::random(16)); // random-password
            $uObj->save();


            /** 3) */
            $bmdAuth = new BmdAuth();
            $bmdAuth->user_id = $uObj->id;
            $bmdAuth->token = $socialiteUser->token;
            $bmdAuth->refresh_token = $socialiteUser->refresh_token;
            $bmdAuth->expires_in = $socialiteUser->expires_in;
            $bmdAuth->auth_provider_type_id = AuthProviderType::GOOGLE;
            $bmdAuth->save();


            /** 4) */
            $urlParams = '?accessToken=' . $socialiteUser->token;
            $urlParams .= '&refreshToken=' . $socialiteUser->refreshToken;
            $urlParams .= '&expiresIn=' . $socialiteUser->expiresIn;
            $urlParams .= '&authProviderId=2';

            $url = self::APP_FRONTEND_SIGNUP_RESULT_URL . $urlParams;

            return Redirect::to($url);
        } catch (Exception $e) {

            $customError = "Oops, there's a problem on our end. Please try again.";
            $urlParams = '?customError=' . $customError;
            $urlParams .= '&exception=' . $e->getMessage();

            $url = self::APP_FRONTEND_SIGNUP_RESULT_URL . $urlParams;

            return Redirect::to($url);
        }
    }



    public function handleProviderCallbackFromGoogle(Request $r)
    {
        try {
            /** 1) TODO: Make sure the dynamodb session/cache driver is configured. */

            // TODO:ON-ITERATION-003: Use a bmd-signup-hash-code for this workflow to allow only legit signups.
            // Maybe use session->put() for this.



            $socialiteUser = Socialite::driver('google')->user();


            /** 2) Create a reference-only-type user-obj (not really signed-up user using Laravel app). */
            $uObj = new User();
            $uObj->email = $socialiteUser->email;
            $uObj->password = Hash::make(Str::random(16)); // random-password
            $uObj->save();


            /** 3) */
            $bmdAuth = new BmdAuth();
            $bmdAuth->user_id = $uObj->id;
            $bmdAuth->token = $socialiteUser->token;
            $bmdAuth->refresh_token = $socialiteUser->refresh_token;
            $bmdAuth->expires_in = $socialiteUser->expires_in;
            $bmdAuth->auth_provider_type_id = AuthProviderType::GOOGLE;
            $bmdAuth->save();


            /** 4) */
            $urlParams = '?accessToken=' . $socialiteUser->token;
            $urlParams .= '&refreshToken=' . $socialiteUser->refreshToken;
            $urlParams .= '&expiresIn=' . $socialiteUser->expiresIn;
            $urlParams .= '&authProviderId=2';

            $url = self::APP_FRONTEND_SIGNUP_RESULT_URL . $urlParams;

            return Redirect::to($url);
        } catch (Exception $e) {

            $customError = "Oops, there's a problem on our end. Please try again.";
            $urlParams = '?customError=' . $customError;
            $urlParams .= '&exception=' . $e->getMessage();

            $url = self::APP_FRONTEND_SIGNUP_RESULT_URL . $urlParams;

            return Redirect::to($url);
        }
    }
}
