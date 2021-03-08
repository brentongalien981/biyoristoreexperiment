<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use App\BmdAuth;
use App\Profile;
use App\StripeCustomer;
use App\AuthProviderType;
use App\TestSocialiteUser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;

class BmdSocialiteController extends Controller
{
    // TODO:ON-DEPLOYMENT: Edit this.
    private const TEST_APP_FRONTEND_SIGNUP_RESULT_URL = 'http://localhost:3000/bmd-socialite-signup-result';
    private const APP_FRONTEND_SIGNUP_RESULT_URL = 'https://bmd.com/bmd-socialite-signup-result';



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



    public function testhandleProviderCallback(Request $r)
    {
        $stripeInstance = null;
        $stripeCustomer = null;
        $overallProcessLogs = [];


        try {

            $providerType = [
                'id' => ($r->provider == 'google' ? 2 : 3),
                'name' => $r->provider,
            ];

            $email = Str::random(8) . '@' . $providerType['name'] . '.com';

            
            // TODO: Check if email already exists.
            if (User::doesExistWithEmail($email)) {
                return $this->handleCallbackForExistingBmdSocialiteEmail($email);
            }
            //ish


            //
            $socialiteUser = new TestSocialiteUser();
            $socialiteUser->email = $email;
            $socialiteUser->token = Str::random(32);
            $socialiteUser->refresh_token = Str::random(32);
            $socialiteUser->expires_in = 345678;



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
            $bmdAuth->auth_provider_type_id = $providerType['id'];
            $bmdAuth->save();

            $overallProcessLogs[] = 'created bmd-auth obj';


            //
            $profile = Profile::create([
                'user_id' => $uObj->id
            ]);
            $overallProcessLogs[] = 'created profile obj';



            // Create stripe-objs.
            // TODO:ON-DEPLOYMENT: Use the production-key here.
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
            $urlParams = '?bmdToken=' . $socialiteUser->token;
            $urlParams .= '&refreshToken=' . $socialiteUser->refresh_token;
            $urlParams .= '&expiresIn=' . $socialiteUser->expires_in;
            $urlParams .= '&authProviderId=' . $providerType['id'];
            $urlParams .= '&email=' . $socialiteUser->email;

            $url = self::TEST_APP_FRONTEND_SIGNUP_RESULT_URL . $urlParams;
            $overallProcessLogs[] = 'created url-params';


            //
            DB::commit();
            $overallProcessLogs[] = 'commited db-transaction';


            //
            return Redirect::to($url);
        } catch (Exception $e) {

            DB::rollBack();
            $overallProcessLogs[] = 'rolled-back db-transaction';


            $caughtCustomErrors[] = $e->getMessage();
            $overallProcessLogs[] = 'caught custom-error';

            $customError = "Oops, there's a problem on our end. Please try again.";
            $urlParams = '?customError=' . $customError;
            $urlParams .= '&exception=' . $e->getMessage();

            $url = self::TEST_APP_FRONTEND_SIGNUP_RESULT_URL . $urlParams;

            return Redirect::to($url);
        }
    }



    public function handleProviderCallbackFromGoogle(Request $r)
    {
        try {

            // TODO: Make sure the dynamodb session/cache driver is configured.
            // TODO: Add the missing steps from the METHOD: testhandleProviderCallback().

            /** 1) */
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
            $urlParams .= '&refreshToken=' . $socialiteUser->refresh_token;
            $urlParams .= '&expiresIn=' . $socialiteUser->expires_in;
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
