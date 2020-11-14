<?php

namespace App\Http\Controllers;

use Error;
use Exception;
use Illuminate\Http\Request;

class PaymentIntentController extends Controller
{
    public function create(Request $request)
    {

        // This is your real test secret API key.
        \Stripe\Stripe::setApiKey(env('STRIPE_SK'));


        // TODO: Create order record with status "waiting-for-payment".



        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => 69,
                'currency' => 'cad',
            ]);


            return [
                'clientSecret' => $paymentIntent->client_secret,
                'street' => $request->street,
                'city' => $request->city,
                'email' => $request->email,
                'phone' => $request->phone,
                'cartItemsData' => $request->cartItemsData
            ];
        } catch (Exception $e) {
            return ['customError' => $e->getMessage()];
        }
    }
}
