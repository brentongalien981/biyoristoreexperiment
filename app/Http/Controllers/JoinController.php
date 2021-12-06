<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use App\BmdAuth;
use App\Profile;
use App\PasswordReset;
use App\StripeCustomer;
use App\AuthProviderType;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\BmdHttpResponseCodes\GeneralHttpResponseCodes;
use App\Http\BmdHttpResponseCodes\JoinHttpResponseCodes;
use App\Mail\PasswordResetLink;

class JoinController extends Controller
{
    public const LOGIN_RESULT_CODE_INVALID_PASSWORD = -1;
    public const LOGIN_RESULT_CODE_INVALID_BMD_AUTH_PROVIDER = -2;
    public const LOGIN_RESULT_CODE_FAIL = -3;
    public const LOGIN_RESULT_CODE_SUCCESS = 1;



    public function verify(Request $r)
    {

        $bmdAuth = BmdAuthProvider::getInstance();
        $user = BmdAuthProvider::user();

        return [
            'isResultOk' => $r->bmdToken === $bmdAuth->token ? true : false,
            'objs' => [
                'email' => $user->email,
                'bmdToken' => $bmdAuth->token,
                'bmdRefreshToken' => $bmdAuth->refresh_token,
                'expiresIn' => $bmdAuth->expires_in,
                'authProviderId' => $bmdAuth->auth_provider_type_id,
            ],
        ];
    }



    public function emailUserResetLink(Request $r)
    {

        $isResultOk = false;
        $resultCode = null;


        $v = $r->validate([
            'email' => 'email|exists:users'
        ]);


        try {
            if (BmdAuthProvider::check()) {
                throw new Exception('This functionality is for guest users only.');
            }

            // Set user's password-reset-token
            $passwordReset = new PasswordReset();
            $passwordReset->email = $r->email;
            $passwordReset->token = Str::uuid()->toString();
            $passwordReset->save();


            // Send email.
            $link = env('APP_FRONTEND_URL') . '/password-reset?t=' . $passwordReset->token;
            Mail::to($r->email)
                ->send(new PasswordResetLink($link));

            $isResultOk = true;
        } catch (\Throwable $th) {
            $resultCode = GeneralHttpResponseCodes::getGeneralExceptionCode($th);
        }

        return [
            'isResultOk' => $isResultOk,
            'objs' => [],
            'resultCode' => $resultCode
        ];
    }




    public function updatePassword(Request $r)
    {

        $v = $r->validate([
            'resetToken' => 'required|string|max:64',
            'password' => 'string|min:8|max:32',
            // 'password' => 'required|alpha_num|min:8|max:32',
        ]);


        $isResultOk = false;
        $resultCode = null;
        $user = null;
        $bmdAuth = null;
        $returnObjs = [];
        $overallProcessLogs = [];

        try {

            DB::beginTransaction();


            // Reference the user through the reset-password-token.
            $pr = PasswordReset::where('token', $r->resetToken)->orderBy('created_at', 'desc')->get()[0] ?? null;
            if (!$pr) {
                $resultCode = JoinHttpResponseCodes::INVALID_RESET_TOKEN;
                throw new Exception('Invalid Reset Token');
            }

            if ($pr->isExpired()) {
                $resultCode = JoinHttpResponseCodes::EXPIRED_RESET_TOKEN;
                throw new Exception('Reset Token is expired.');
            }

            $user = User::where('email', $pr->email)->get()[0] ?? null;
            if (!$user) {
                $resultCode = JoinHttpResponseCodes::USER_NOT_FOUND;
                throw new Exception('User not found.');
            }

            $overallProcessLogs[] = 'Referenced user.';


            // Update the user's password.
            $user->password = Hash::make($v['password']);
            $user->save();
            $overallProcessLogs[] = 'Updated password.';


            // Revoke all user's old tokens.
            self::revokeAllPassportTokens($user->id);
            $overallProcessLogs[] = 'user-tokens revoked';


            // Create a new oauth-token record for user.
            $oauthProps = self::createPasswordAccessPassportToken($user->email, $r->password, $r);
            $overallProcessLogs[] = 'created new user-token';


            // Delete the old bmd-auth cache-record
            $bmdAuth = BmdAuth::where('user_id', $user->id)->get()[0] ?? null;
            $bmdAuth->deleteOldCacheRecord();
            $overallProcessLogs[] = 'deleted old-bmd-auth cache-record';


            // Update BmdAuth's token.
            $bmdAuth->token = $oauthProps['access_token'];
            $bmdAuth->refresh_token = $oauthProps['refresh_token'];
            $bmdAuth->expires_in = getdate()[0] + BmdAuth::NUM_OF_SECS_PER_MONTH;
            $bmdAuth->frontend_pseudo_expires_in = $bmdAuth->expires_in;
            $bmdAuth->save();
            $overallProcessLogs[] = 'updated bmd-auth record';

            $bmdAuth->saveToCache();
            $overallProcessLogs[] = 'saved bmd-auth to cache';


            //
            DB::commit();
            $overallProcessLogs[] = 'commited db-transaction';
            $isResultOk = true;


            $returnObjs = [
                'email' => $user->email,
                'bmdToken' => $bmdAuth->token,
                'bmdRefreshToken' => $bmdAuth->refresh_token,
                'expiresIn' => $bmdAuth->expires_in,
                'authProviderId' => $bmdAuth->auth_provider_type_id,
            ];
        } catch (Exception $e) {

            DB::rollBack();
            $overallProcessLogs[] = 'rolled-back db-transaction';
            $overallProcessLogs[] = 'Exception ==> ' . $e->getMessage();
        }




        return [
            'isResultOk' => $isResultOk,
            'objs' => $returnObjs,
            'resultCode' => $resultCode,
            // BMD-ON-ITER: Staging, Deployment: Comment-out.
            'overallProcessLogs' => $overallProcessLogs,
        ];
    }




    private static function revokeAllPassportTokens($userId)
    {
        $tokens = DB::table('oauth_access_tokens')->where('user_id', $userId)->get();

        foreach ($tokens as $t) {
            $tokenRepository = app('Laravel\Passport\TokenRepository');
            $refreshTokenRepository = app('Laravel\Passport\RefreshTokenRepository');

            // Revoke an access token...
            $tokenRepository->revokeAccessToken($t->id);

            // Revoke all of the token's refresh tokens...
            $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($t->id);
        }
    }



    private static function createPasswordAccessPassportToken($email, $password, $request)
    {
        // 3) Create Passport-Password-Access-Token.
        $request->request->add([
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_GRANT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_GRANT_PASSWORD_CLIENT_SECRET'),
            'username' => $email,
            'password' => $password,
            'scope' => '*',
        ]);

        $tokenRequest = Request::create(
            url('oauth/token'),
            'post'
        );

        $response = Route::dispatch($tokenRequest);;


        $rawObjs = json_decode($response->original);

        $oauthProps = [];
        foreach ($rawObjs as $k => $v) {
            $oauthProps[$k] = $v;
        }

        return $oauthProps;
    }



    public function login(Request $request)
    {

        $validatedData = $request->validate([
            'email' => 'email|exists:users',
            'password' => 'max:32',
            'stayLoggedIn' => 'boolean',
        ]);

        $possibleUser = User::where('email', $validatedData['email'])->get()[0];

        $isResultOk = false;
        $bmdAuth = null;
        $overallProcessLogs = [];
        $resultCode = 0;


        try {

            // Check if BmdAuth has auth-provider-type Bmd.
            $bmdAuth = BmdAuth::where('user_id', $possibleUser->id)->get()[0] ?? null;
            if (
                !isset($bmdAuth) ||
                $bmdAuth->auth_provider_type_id != AuthProviderType::BMD
            ) {

                $resultCode = self::LOGIN_RESULT_CODE_INVALID_BMD_AUTH_PROVIDER;
                throw new Exception('Invalid bmd-auth provider');
            }


            if (Hash::check($validatedData['password'], $possibleUser->password)) {
                $overallProcessLogs[] = 'password ok';


                // Revoke all user's old tokens.
                self::revokeAllPassportTokens($possibleUser->id);
                $overallProcessLogs[] = 'user-tokens revoked';

                // Create a new oauth-token record for user.
                $oauthProps = self::createPasswordAccessPassportToken($validatedData['email'], $validatedData['password'], $request);
                $overallProcessLogs[] = 'created new user-token';

                // Delete the old bmd-auth cache-record
                $bmdAuth->deleteOldCacheRecord();
                $overallProcessLogs[] = 'deleted old-bmd-auth cache-record';


                // Update BmdAuth's token.
                $bmdAuth->token = $oauthProps['access_token'];
                $bmdAuth->refresh_token = $oauthProps['refresh_token'];
                $bmdAuth->expires_in = getdate()[0] + BmdAuth::NUM_OF_SECS_PER_MONTH;
                $bmdAuth->frontend_pseudo_expires_in = $bmdAuth->expires_in;
                $bmdAuth->save();
                $overallProcessLogs[] = 'updated bmd-auth record';

                $stayLoggedIn = $validatedData['stayLoggedIn'] ?? false;
                $bmdAuth->saveToCache($stayLoggedIn);
                $overallProcessLogs[] = 'saved bmd-auth to cache';


                $resultCode = self::LOGIN_RESULT_CODE_SUCCESS;
                $isResultOk = true;
            } else {
                $overallProcessLogs[] = 'invalid password';
                $resultCode = self::LOGIN_RESULT_CODE_INVALID_PASSWORD;
                $bmdAuth = null;
            }
        } catch (Exception $e) {
            $overallProcessLogs[] = 'caught-custom-error: ' . $e->getMessage();
            $bmdAuth = null;
        }



        //
        return [
            'isResultOk' => $isResultOk,
            'overallProcessLogs' => $overallProcessLogs, // BMD-ON-ITER: Staging, Deployment: Comment this out.
            'resultCode' => $resultCode,
            'objs' => [
                'email' => $possibleUser->email,
                'bmdToken' => $bmdAuth ? $bmdAuth->token : null,
                'bmdRefreshToken' => $bmdAuth ? $bmdAuth->refresh_token : null,
                'expiresIn' => $bmdAuth ? $bmdAuth->expires_in : null,
                'authProviderId' => $bmdAuth ? $bmdAuth->auth_provider_type_id : null
            ],
        ];
    }



    public static function deleteStripeDotComCustomer($stripeInstance, $stripeCustomer)
    {
        $returnData = [
            'isResultOk' => false,
            'resultMsg' => 'DEFAULT-MSG: CLASS: JoinController, METHOD: deleteStripeDotComCustomer()',
        ];


        try {
            if (isset($stripeCustomer)) {
                $stripeInstance->customers->delete(
                    $stripeCustomer->id,
                    []
                );

                $returnData['isResultOk'] = true;
                $returnData['resultMsg'] = 'stripe.com customer successfully deleted';
            }
        } catch (Exception $e) {
            $returnData['resultMsg'] = 'stripe.com customer failed to delete ==> ' . $e->getMessage();
        }


        return $returnData;
    }



    public function save(Request $request)
    {

        // 0) Validate
        $validatedData = $request->validate([
            'email' => 'email|min:8|max:64|unique:users',
            'password' => 'string|min:8|max:32',
            // 'password' => 'alpha_num|min:8|max:32',
        ]);


        $stripeInstance = null;
        $stripeCustomer = null;
        $overallProcessLogs = [];

        try {

            // 1) 
            DB::beginTransaction();
            $overallProcessLogs[] = 'began db-transaction';


            // 2) 
            $user = User::create([
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);
            $overallProcessLogs[] = 'created user';


            $oauthProps = self::createPasswordAccessPassportToken($validatedData['email'], $validatedData['password'], $request);
            $overallProcessLogs[] = 'dispatched oauth-token request';


            // Create BmdAuth obj.
            $bmdAuth = new BmdAuth();
            $bmdAuth->user_id = $user->id;
            $bmdAuth->token = $oauthProps['access_token'];
            $bmdAuth->refresh_token = $oauthProps['refresh_token'];
            $bmdAuth->expires_in = getdate()[0] + BmdAuth::NUM_OF_SECS_PER_MONTH;
            $bmdAuth->frontend_pseudo_expires_in = $bmdAuth->expires_in;
            $bmdAuth->auth_provider_type_id = AuthProviderType::BMD;
            $bmdAuth->save();
            $overallProcessLogs[] = 'created bmd-auth obj';

            $bmdAuth->saveToCache();
            $overallProcessLogs[] = 'saved bmd-auth to cache';


            // Create profile.
            $profile = Profile::create([
                'user_id' => $user->id
            ]);
            $overallProcessLogs[] = 'created profile obj';


            // BMD-ON-STAGING: Use the production-key here.
            // 7) Create stripe objs.
            $stripeInstance = new \Stripe\StripeClient(env('STRIPE_SK'));

            $stripeCustomer = $stripeInstance->customers->create([
                'email' => $user->email,
                'description' => 'Created from the backend.'
            ]);

            $overallProcessLogs[] = 'created stripe obj';



            // 8) Create stripe-map-objs.
            $stripeCustomerMapObj = new StripeCustomer();
            $stripeCustomerMapObj->user_id = $user->id;
            $stripeCustomerMapObj->stripe_customer_id = $stripeCustomer->id;
            $stripeCustomerMapObj->save();
            $overallProcessLogs[] = 'created stripe-map obj';



            // 8) 
            DB::commit();
            $overallProcessLogs[] = 'commited db-transaction';


            // 9)
            return [
                'isResultOk' => true,
                // 'comment' => "CLASS: JoinController, METHOD: save()",
                // 'validatedData' => $validatedData,
                // 'profile' => $profile,
                // 'stripeCustomer' => $stripeCustomer,
                // 'stripeCustomerMapObj' => $stripeCustomerMapObj,
                // 'overallProcessLogs' => $overallProcessLogs,
                'objs' => [
                    'email' => $user->email,
                    'bmdToken' => $bmdAuth->token,
                    'bmdRefreshToken' => $bmdAuth->refresh_token,
                    'expiresIn' => $bmdAuth->expires_in,
                    'authProviderId' => $bmdAuth->auth_provider_type_id,
                ],
            ];
        } catch (Exception $e) {

            DB::rollBack();
            $overallProcessLogs[] = 'rolled-back db-transaction';


            $caughtCustomErrors[] = $e->getMessage();
            $overallProcessLogs[] = 'caught exxception ==> ' . $e->getMessage();

            $deletionData = self::deleteStripeDotComCustomer($stripeInstance, $stripeCustomer);
            $overallProcessLogs[] = $deletionData['resultMsg'];


            return [
                'isResultOk' => false,
                'caughtCustomErrors' => $caughtCustomErrors,
                // 'overallProcessLogs' => $overallProcessLogs,
            ];
        }
    }
}
