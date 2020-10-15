<?php

namespace App\Http\Controllers;

use Error;
use Illuminate\Http\Request;

class PaymentIntentController extends Controller
{
    public function create()
    {
        // This is your real test secret API key.
        \Stripe\Stripe::setApiKey(env('STRIPE_SK'));


        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => 69,
                'currency' => 'usd',
            ]);


            return [
                'clientSecret' => $paymentIntent->client_secret,
            ];
        } catch (Error $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
