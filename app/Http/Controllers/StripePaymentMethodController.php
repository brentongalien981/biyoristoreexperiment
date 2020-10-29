<?php

namespace App\Http\Controllers;

use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StripePaymentMethodController extends Controller
{
    public function save(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'id' => 'nullable|numeric',
            'cardNumber' => 'required|regex:/^[0-9]{16,128}$/',
            'expirationMonth' => 'required|integer|min:1|max:12',
            'expirationYear' => 'required|integer|min:2020|max:2030',
            'cvc' => 'required|integer',
            'postalCode' => 'required'
        ]);



        try {

            $stripe = new \Stripe\StripeClient(env('STRIPE_SK'));

            $stripePaymentMethod = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'number' => $validatedData['cardNumber'],
                    'exp_month' => $validatedData['expirationMonth'],
                    'exp_year' => $validatedData['expirationYear'],
                    'cvc' => $validatedData['cvc'],
                ],
                'billing_details' => [
                    'address' => [
                        'postal_code' => $validatedData['postalCode']
                    ]
                ]
            ]);


            $stripeCustomerId = $user->stripeCustomer->stripe_customer_id;

            $stripe->paymentMethods->attach(
                $stripePaymentMethod->id,
                ['customer' => $stripeCustomerId]
            );



            //
            return [
                'isResultOk' => true,
                'validatedData' => $validatedData,
                'stripeCustomerId' => $stripeCustomerId,
                'stripePaymentMethodId' => $stripePaymentMethod->id
            ];
            
        } catch (Error $e) {
            return [
                'isResultOk' => false,
                'validatedData' => $validatedData,
                'errors' => $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }

    }
}
