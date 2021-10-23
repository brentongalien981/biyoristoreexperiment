<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use App\BmdAuth;
use App\Profile;
use App\StripeCustomer;
use App\AuthProviderType;
use App\MyHelpers\General\GeneralHelper2;
use App\TestSocialiteUser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;

class BmdSocialiteController extends Controller
{
    // BMD-ON-STAGING: Edit this..
    // NOTE: The Laravel env() function doesn't work outside a function.
    // private const TEST_APP_FRONTEND_SIGNUP_RESULT_URL = 'http://localhost:3000/bmd-socialite-signup-result';
    // private const APP_FRONTEND_URL = 'http://localhost:3000';
    // private const APP_FRONTEND_SIGNUP_RESULT_URL = GeneralHelper2::getAppFrontendUrl2() . '/bmd-socialite-signup-result';
    // private const APP_FRONTEND_LOGIN_RESULT_URL = GeneralHelper2::getAppFrontendUrl2() . '/bmd-socialite-login-result';

    private const AUTH_RESULT_FOR_EXISTING_SOCIALITE_USER = 1;
    private const AUTH_RESULT_FOR_OK_SOCIALITE_SIGNUP = 2;
    private const AUTH_RESULT_FOR_FAIL_SOCIALITE_SIGNUP = 3;
    private const AUTH_RESULT_FOR_OK_SOCIALITE_LOGIN = 4;
    private const AUTH_RESULT_FOR_FAIL_SOCIALITE_LOGIN = 5;



    public static function getTestSignupResultUrl()
    {
        return GeneralHelper2::getAppFrontendUrl2() . '/bmd-socialite-signup-result';
    }



    public static function getTestLoginpResultUrl()
    {
        return GeneralHelper2::getAppFrontendUrl2() . '/bmd-socialite-login-result';
    }



    public function testloginWithAuthProvider(Request $r)
    {
        // return $this->testhandleSocialiteLoginCallback($r);
        return $this->testhandleProviderCallback($r);
    }




    public function testsignupWithAuthProvider(Request $r)
    {
        return $this->testhandleProviderCallback($r);
    }



    public function signupWithAuthProvider(Request $r)
    {
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



    public function loginWithAuthProvider(Request $r)
    {
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



    public function redirectForAuthResult($data)
    {
        $urlParams = '';

        switch ($data['authResult']) {
            case self::AUTH_RESULT_FOR_EXISTING_SOCIALITE_USER:
            case self::AUTH_RESULT_FOR_OK_SOCIALITE_SIGNUP:
            case self::AUTH_RESULT_FOR_OK_SOCIALITE_LOGIN:

                $bmdAuth = $data['bmdAuth'];
                $socialiteUser = $data['socialiteUser'];
                $stayLoggedIn = (isset($data['stayLoggedIn']) && $data['stayLoggedIn'] == true) ? 1 : 0;

                $urlParams .= '?bmdToken=' . $bmdAuth->token;
                $urlParams .= '&bmdRefreshToken=' . $bmdAuth->refresh_token;
                $urlParams .= '&expiresIn=' . $bmdAuth->expires_in;
                $urlParams .= '&authProviderId=' . $bmdAuth->auth_provider_type_id;
                $urlParams .= '&email=' . $socialiteUser->email;
                $urlParams .= '&stayLoggedIn=' . $stayLoggedIn;
                if (isset($data['resultCode'])) {
                    $urlParams .= '&resultCode=' . $data['resultCode'];
                }
                break;

            case self::AUTH_RESULT_FOR_FAIL_SOCIALITE_SIGNUP:
            case self::AUTH_RESULT_FOR_FAIL_SOCIALITE_LOGIN:
                $urlParams .= '?caughtCustomError=' . $data['caughtCustomError'];
                break;
        }


        $urlParams .= '&authResult=' . $data['authResult'];
        $urlParams .= '&overallProcessLogs=' . implode(':::', $data['overallProcessLogs'] ?? []);
        $url = null;

        switch ($data['authResult']) {
            case self::AUTH_RESULT_FOR_EXISTING_SOCIALITE_USER:
            case self::AUTH_RESULT_FOR_OK_SOCIALITE_LOGIN:
            case self::AUTH_RESULT_FOR_FAIL_SOCIALITE_LOGIN:
                $url = self::getTestLoginpResultUrl() . $urlParams;
                break;
            case self::AUTH_RESULT_FOR_OK_SOCIALITE_SIGNUP:
            case self::AUTH_RESULT_FOR_FAIL_SOCIALITE_SIGNUP:
                $url = self::getTestSignupResultUrl() . $urlParams;
                break;
        }


        return Redirect::to($url);
    }



    public function testhandleSocialiteLoginCallback(Request $r, $isComingFromSignupWorkflow = false)
    {
        $overallProcessLogs[] = 'In METHOD: testhandleSocialiteLoginCallback()';
        if ($isComingFromSignupWorkflow) {
            $overallProcessLogs[] = 'Oops! User was trying to sign-up with existing record...';
        }


        $providerType = [
            'id' => ($r->provider == 'google' ? 2 : 3),
            'name' => $r->provider,
        ];


        try {
            // Reference a random existing user with appropriate email for google or facebook.
            $possibleUsers = User::where('email', 'like', '%' . $providerType['name'] . '%')->get();

            // BMD-ON-STAGING: Test and make sure that this workflow happens.
            // If the user has no existing record, redirect him to a sign-up workflow.
            if (
                !isset($possibleUsers)
                || count($possibleUsers) === 0 // BMD-ON-STAGING: This should be just ==> count() !== 1
                || !isset($possibleUsers[0])
            ) {
                $isComingFromLoginWorkflow = true;
                return $this->testhandleProviderCallback($r, $isComingFromLoginWorkflow);
            }

            $user = $possibleUsers[0];
            $overallProcessLogs[] = 'referenced an existing user with ' . $providerType['name'] . ' as provider';


            $socialiteUser = new TestSocialiteUser();
            $socialiteUser->email = $user->email;
            $socialiteUser->token = Str::random(1024);
            $socialiteUser->refresh_token = Str::random(32);
            $socialiteUser->expires_in = getdate()[0] + BmdAuth::NUM_OF_SECS_PER_MONTH;
            $overallProcessLogs[] = 'using a fake-pre-existing socialite-user';


            // Delete the old bmd-auth cache-record
            $bmdAuth = BmdAuth::where('user_id', $user->id)->get()[0];
            $bmdAuth->deleteOldCacheRecord();
            $overallProcessLogs[] = 'deleted old-bmd-auth cache-record';


            // Update BmdAuth's token.
            $bmdAuth->token = $socialiteUser->token;
            $bmdAuth->refresh_token = $socialiteUser->refresh_token;
            $bmdAuth->expires_in = $socialiteUser->expires_in;
            $bmdAuth->frontend_pseudo_expires_in = $bmdAuth->expires_in;
            $bmdAuth->save();
            $overallProcessLogs[] = 'updated bmd-auth record';

            // saved bmd-auth to cache
            $stayLoggedIn = ($r->stayLoggedIn == 1 ? true : false);
            $bmdAuth->saveToCache($stayLoggedIn);
            $overallProcessLogs[] = 'saved bmd-auth to cache';


            return $this->redirectForAuthResult([
                'authResult' => self::AUTH_RESULT_FOR_OK_SOCIALITE_LOGIN,
                'bmdAuth' => $bmdAuth,
                'socialiteUser' => $socialiteUser,
                'overallProcessLogs' => $overallProcessLogs,
                'stayLoggedIn' => $stayLoggedIn,
                'resultCode' => JoinController::LOGIN_RESULT_CODE_SUCCESS,
            ]);
        } catch (Exception $e) {

            $overallProcessLogs[] = 'caught exxception ==> ' . $e->getMessage();

            return $this->redirectForAuthResult([
                'authResult' => self::AUTH_RESULT_FOR_FAIL_SOCIALITE_LOGIN,
                'overallProcessLogs' => $overallProcessLogs,
                'caughtCustomError' => $e->getMessage(),
                'resultCode' => JoinController::LOGIN_RESULT_CODE_FAIL,
            ]);
        }
    }



    public function testhandleProviderCallback(Request $r, $isComingFromLoginWorkflow = false)
    {

        $overallProcessLogs[] = 'In METHOD: testhandleProviderCallback()';
        if ($isComingFromLoginWorkflow) {
            $overallProcessLogs[] = 'Oops! User was trying to sign-in without existing record';
        }

        $stripeInstance = null;
        $stripeCustomer = null;


        try {

            $providerType = [
                'id' => ($r->provider == 'google' ? 2 : 3),
                'name' => $r->provider,
            ];

            $email = Str::random(8) . '@' . $providerType['name'] . '.com';

            $socialiteUser = new TestSocialiteUser();
            $socialiteUser->email = $email;
            $socialiteUser->token = Str::random(1024);
            $socialiteUser->refresh_token = Str::random(32);
            $socialiteUser->expires_in = getdate()[0] + BmdAuth::NUM_OF_SECS_PER_MONTH;


            // If the user already exists, redirect him to a log-in workflow.
            // BMD-ON-STAGING: Test and make sure that this workflow happens.
            if (User::doesExistWithEmail($email)) {
                return $this->testhandleSocialiteLoginCallback($r, true);
            }



            //
            DB::beginTransaction();
            $overallProcessLogs[] = 'began db-transaction';


            // Create a reference-only-type user-obj (not really signed-up user using Laravel app).
            $uObj = new User();
            $uObj->email = $socialiteUser->email;
            $uObj->password = Hash::make(Str::random(16)); // random-password
            $uObj->save();

            $overallProcessLogs[] = 'created user';


            //
            $bmdAuth = new BmdAuth();
            $bmdAuth->user_id = $uObj->id;
            $bmdAuth->token = $socialiteUser->token;
            $bmdAuth->refresh_token = $socialiteUser->refresh_token;
            $bmdAuth->expires_in = $socialiteUser->expires_in;
            $bmdAuth->frontend_pseudo_expires_in = $bmdAuth->expires_in;
            $bmdAuth->auth_provider_type_id = $providerType['id'];
            $bmdAuth->save();

            $overallProcessLogs[] = 'created bmd-auth obj';

            $bmdAuth->saveToCache();
            $overallProcessLogs[] = 'saved bmd-auth to cache';


            //
            Profile::create([
                'user_id' => $uObj->id
            ]);
            $overallProcessLogs[] = 'created profile obj';



            // Create stripe-objs.
            // BMD-ON-STAGING: Use the production-key here.
            $stripeInstance = new \Stripe\StripeClient(env('STRIPE_SK'));

            $stripeCustomer = $stripeInstance->customers->create([
                'email' => $uObj->email,
                'description' => 'Created from the backend.'
            ]);

            $overallProcessLogs[] = 'created stripe obj';



            //
            $stripeCustomerMapObj = new StripeCustomer();
            $stripeCustomerMapObj->user_id = $uObj->id;
            $stripeCustomerMapObj->stripe_customer_id = $stripeCustomer->id;
            $stripeCustomerMapObj->save();
            $overallProcessLogs[] = 'created stripe-map obj';


            //
            DB::commit();
            $overallProcessLogs[] = 'commited db-transaction';



            // 
            return $this->redirectForAuthResult([
                'authResult' => self::AUTH_RESULT_FOR_OK_SOCIALITE_SIGNUP,
                'bmdAuth' => $bmdAuth,
                'socialiteUser' => $socialiteUser,
                'providerTypeId' => $providerType['id'],
                'overallProcessLogs' => $overallProcessLogs,
            ]);
        } catch (Exception $e) {

            $overallProcessLogs[] = 'caught exxception ==> ' . $e->getMessage();
            DB::rollBack();
            $overallProcessLogs[] = 'rolled-back db-transaction';

            // Delete the created stripe.com's customer-obj.
            $deletionData = JoinController::deleteStripeDotComCustomer($stripeInstance, $stripeCustomer);
            $overallProcessLogs[] = $deletionData['resultMsg'];


            return $this->redirectForAuthResult([
                'authResult' => self::AUTH_RESULT_FOR_FAIL_SOCIALITE_SIGNUP,
                'overallProcessLogs' => $overallProcessLogs,
                'caughtCustomError' => $e->getMessage(),
            ]);
        }
    }



    public function handleProviderCallbackFromFacebook(Request $r)
    {
        return $this->handleProviderCallback(['provider' => 'facebook']);
    }



    public function handleProviderCallbackFromGoogle(Request $r)
    {
        return $this->handleProviderCallback(['provider' => 'google']);
    }



    private function handleProviderCallback($data)
    {
        // BMD-TODO: Make sure the dynamodb session/cache driver is configured.
        try {

            $socialiteUser = Socialite::driver($data['provider'])->user();

            $data['socialiteUser'] = $socialiteUser;

            if (User::doesExistWithEmail($socialiteUser->email)) {
                return $this->handleProductionSocialiteLoginProviderCallback($data);
            }

            return $this->handleProductionSocialiteSignupProviderCallback($data);
        } catch (Exception $e) {
            return $this->redirectForAuthResult([
                'authResult' => self::AUTH_RESULT_FOR_FAIL_SOCIALITE_SIGNUP,
                'caughtCustomError' => $e->getMessage(),
            ]);
        }
    }



    private function handleProductionSocialiteSignupProviderCallback($data)
    {
        $overallProcessLogs[] = 'In METHOD: handleProductionSocialiteSignupProviderCallback()';
        $stripeInstance = null;
        $stripeCustomer = null;


        try {

            $socialiteUser = $data['socialiteUser'];
            DB::beginTransaction();
            $overallProcessLogs[] = 'began db-transaction';

            // Create user. Create a reference-only-type user-obj (not really signed-up user using Laravel app).
            $uObj = new User();
            $uObj->email = $socialiteUser->email;
            $uObj->password = Hash::make(Str::random(16)); // random-password
            $uObj->save();
            $overallProcessLogs[] = 'created user';


            // Create bmdauth.
            $bmdAuth = new BmdAuth();
            $bmdAuth->user_id = $uObj->id;
            $bmdAuth->token = $socialiteUser->token;
            $bmdAuth->refresh_token = $socialiteUser->refresh_token;
            $bmdAuth->expires_in = $socialiteUser->expires_in;
            $bmdAuth->frontend_pseudo_expires_in = $bmdAuth->expires_in;
            $bmdAuth->auth_provider_type_id = AuthProviderType::getProviderTypeId($data['provider']);
            $bmdAuth->save();
            $overallProcessLogs[] = 'created bmd-auth obj';

            $bmdAuth->saveToCache();
            $overallProcessLogs[] = 'saved bmd-auth to cache';


            // Create profile.
            Profile::create(['user_id' => $uObj->id]);
            $overallProcessLogs[] = 'created profile obj';


            // Create stripe-objs.
            $stripeInstance = GeneralHelper2::getStripeInstanceBasedOnAppEnv();
            $stripeCustomer = $stripeInstance->customers->create([
                'email' => $uObj->email,
                'description' => 'Created from the backend.'
            ]);
            $overallProcessLogs[] = 'created stripe obj';


            $stripeCustomerMapObj = new StripeCustomer();
            $stripeCustomerMapObj->user_id = $uObj->id;
            $stripeCustomerMapObj->stripe_customer_id = $stripeCustomer->id;
            $stripeCustomerMapObj->save();
            $overallProcessLogs[] = 'created stripe-map obj';


            DB::commit();
            $overallProcessLogs[] = 'commited db-transaction';



            return $this->redirectForAuthResult([
                'authResult' => self::AUTH_RESULT_FOR_OK_SOCIALITE_SIGNUP,
                'bmdAuth' => $bmdAuth,
                'socialiteUser' => $socialiteUser,
                'providerTypeId' => $bmdAuth->auth_provider_type_id,
                'overallProcessLogs' => $overallProcessLogs,
            ]);
        } catch (Exception $e) {

            $overallProcessLogs[] = 'caught exception ==> ' . $e->getMessage();
            DB::rollBack();
            $overallProcessLogs[] = 'rolled-back db-transaction';

            // Delete the created stripe.com's customer-obj.
            $deletionData = JoinController::deleteStripeDotComCustomer($stripeInstance, $stripeCustomer);
            $overallProcessLogs[] = $deletionData['resultMsg'];


            return $this->redirectForAuthResult([
                'authResult' => self::AUTH_RESULT_FOR_FAIL_SOCIALITE_SIGNUP,
                'overallProcessLogs' => $overallProcessLogs,
                'caughtCustomError' => $e->getMessage(),
            ]);
        }
    }



    private function handleProductionSocialiteLoginProviderCallback($data)
    {
        $overallProcessLogs[] = 'In METHOD: handleProductionSocialiteLoginProviderCallback()';


        try {
            $socialiteUser = $data['socialiteUser'];
            $user = User::where('email', $socialiteUser->email)->get()[0];
            $overallProcessLogs[] = 'referenced an existing user';


            // Delete the old bmd-auth cache-record
            $bmdAuth = BmdAuth::where('user_id', $user->id)->get()[0];
            $bmdAuth->deleteOldCacheRecord();
            $overallProcessLogs[] = 'deleted old-bmd-auth cache-record';


            // Update BmdAuth's token.
            $bmdAuth->token = $socialiteUser->token;
            $bmdAuth->refresh_token = $socialiteUser->refresh_token;
            $bmdAuth->expires_in = $socialiteUser->expires_in;
            $bmdAuth->frontend_pseudo_expires_in = $bmdAuth->expires_in;
            $bmdAuth->save();
            $overallProcessLogs[] = 'updated bmd-auth record';


            // saved bmd-auth to cache
            $bmdAuth->saveToCache();
            $overallProcessLogs[] = 'saved bmd-auth to cache';


            return $this->redirectForAuthResult([
                'authResult' => self::AUTH_RESULT_FOR_OK_SOCIALITE_LOGIN,
                'bmdAuth' => $bmdAuth,
                'socialiteUser' => $socialiteUser,
                'overallProcessLogs' => $overallProcessLogs,
                // 'stayLoggedIn' => $stayLoggedIn,
                'resultCode' => JoinController::LOGIN_RESULT_CODE_SUCCESS,
            ]);
        } catch (Exception $e) {

            $overallProcessLogs[] = 'caught exception ==> ' . $e->getMessage();

            return $this->redirectForAuthResult([
                'authResult' => self::AUTH_RESULT_FOR_FAIL_SOCIALITE_LOGIN,
                'overallProcessLogs' => $overallProcessLogs,
                'caughtCustomError' => $e->getMessage(),
                'resultCode' => JoinController::LOGIN_RESULT_CODE_FAIL,
            ]);
        }
    }
}
