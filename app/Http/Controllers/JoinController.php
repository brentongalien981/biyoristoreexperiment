<?php

namespace App\Http\Controllers;

use App\User;
use App\Profile;
use App\StripeCustomer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

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
        //
        $validatedData = $request->validate([
            'email' => 'email|min:8|max:64|unique:users',
            'password' => 'alpha_num|min:4|max:32',
        ]);


        //
        $user = User::create([
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $apiToken = Str::random(80);

        $user->forceFill([
            'api_token' => hash('sha256', $apiToken),
        ])->save();


        Profile::create([
            'user_id' => $user->id
        ]);


        // ish
        \Stripe\Stripe::setApiKey(env('STRIPE_SK'));
        $stripeCustomer = \Stripe\Customer::create([
            'email' => $validatedData['email'],
            'description' => 'Created from the backend.'
        ]);

        $stripeCustomerMapObj = new StripeCustomer();
        $stripeCustomerMapObj->user_id = $user->id;
        $stripeCustomerMapObj->stripe_customer_id = $stripeCustomer->id;
        $stripeCustomerMapObj->save();


        return [
            'isResultOk' => true,
            'comment' => "CLASS: JoinController, METHOD: save()",
            'validatedData' => $validatedData,
            'userId' => $user->id,
            'email' => $validatedData['email'],
            'apiToken' => $apiToken
        ];
    }
}
