<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use App\BmdAuth;
use App\Profile;
use App\StripeCustomer;
use App\AuthProviderType;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;

class JoinController extends Controller
{
    public function login(Request $request)
    {
        //
        $validatedData = $request->validate([
            'email' => 'email|min:8|max:64|exists:users',
            'password' => 'alpha_num|min:4|max:32',
        ]);


        $possibleUser = User::where('email', $validatedData['email'])->get()[0];

        $doesPasswordMatch = false;
        $token = '';
        $isResultOk = false;

        if (Hash::check($request->password, $possibleUser->password)) {
            $doesPasswordMatch = true;

            $token = Str::random(80);

            $possibleUser->forceFill([
                'api_token' => hash('sha256', $token),
            ])->save();

            $isResultOk = true;
        }



        //
        return [
            'isResultOk' => $isResultOk,
            'doesPasswordMatch' => $doesPasswordMatch,
            'userId' => $possibleUser ? $possibleUser->id : 0,
            'email' => $validatedData['email'],
            'apiToken' => $token,
        ];
    }



    public function save(Request $request)
    {
        // 0) Validate
        $validatedData = $request->validate([
            'email' => 'email|min:8|max:64|unique:users',
            'password' => 'alpha_num|min:8|max:32',
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



            // 3) Create Passport-Password-Access-Token.
            $request->request->add([
                'grant_type' => 'password',
                'client_id' => env('PASSPORT_GRANT_PASSWORD_CLIENT_ID'),
                'client_secret' => env('PASSPORT_GRANT_PASSWORD_CLIENT_SECRET'),
                'username' => $user->email,
                'password' => $request->password,
                'scope' => '*',
            ]);

            $tokenRequest = Request::create(
                url('oauth/token'),
                'post'
            );

            $response = Route::dispatch($tokenRequest);;
            $overallProcessLogs[] = 'dispatched oauth-token request';



            // 4) Parse the Passport response.
            $rawObjs = json_decode($response->original);

            $convertedObj = [];
            foreach ($rawObjs as $k => $v) {
                $convertedObj[$k] = $v;
            }


            // 5) Create BmdAuth obj.
            $bmdAuth = new BmdAuth();
            $bmdAuth->user_id = $user->id;
            $bmdAuth->token = $convertedObj['access_token'];
            $bmdAuth->refresh_token = $convertedObj['refresh_token'];
            $bmdAuth->expires_in = $convertedObj['expires_in'];
            $bmdAuth->auth_provider_type_id = AuthProviderType::BMD;
            $bmdAuth->save();
            $overallProcessLogs[] = 'created bmd-auth obj';



            // 6)
            $profile = Profile::create([
                'user_id' => $user->id
            ]);
            $overallProcessLogs[] = 'created profile obj';


            // 7) Create stripe-objs.
            // \Stripe\Stripe::setApiKey(env('STRIPE_SK'));

            // $stripeCustomer = \Stripe\Customer::create([
            //     'email' => $user->email,
            //     'description' => 'Created from the backend.'
            // ]);

            $stripeInstance = new \Stripe\StripeClient(env('STRIPE_SK'));

            $stripeCustomer = $stripeInstance->customers->create([
                'email' => $user->email,
                'description' => 'Created from the backend.'
            ]);

            $overallProcessLogs[] = 'created stripe obj';



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
                'comment' => "CLASS: JoinController, METHOD: save()",
                'validatedData' => $validatedData,
                'profile' => $profile,
                'stripeCustomer' => $stripeCustomer,
                'stripeCustomerMapObj' => $stripeCustomerMapObj,
                'overallProcessLogs' => $overallProcessLogs,
                'objs' => [
                    'email' => $user->email,
                    'bmdToken' => $bmdAuth->token,
                    'expiresIn' => $bmdAuth->expires_in,
                    'authProviderId' => $bmdAuth->auth_provider_type_id,
                ],
            ];
        } catch (Exception $e) {

            DB::rollBack();
            $overallProcessLogs[] = 'rolled-back db-transaction';


            $caughtCustomErrors[] = $e->getMessage();
            $overallProcessLogs[] = 'caught custom-error';

            try {
                // Delete the stripe-obj.
                if (isset($stripeCustomer)) {
                    $stripeInstance->customers->delete(
                        $stripeCustomer->id,
                        []
                    );

                    $overallProcessLogs[] = 'deleted stripe obj';
                }
            } catch (Exception $e) {
                $caughtCustomErrors[] = $e->getMessage();
                $overallProcessLogs[] = 'caught custom-error';
            }


            return [
                'isResultOk' => false,
                'caughtCustomErrors' => $caughtCustomErrors,
                'overallProcessLogs' => $overallProcessLogs,
            ];
        }
    }
}
