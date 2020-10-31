<?php

namespace App\Http\Controllers;

use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StripePaymentMethodController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'id' => 'required',
            'expirationMonth' => 'required|integer|min:1|max:12',
            'expirationYear' => 'required|integer|min:2020|max:2030',
            'postalCode' => 'required'
        ]);



        try {

            $stripe = new \Stripe\StripeClient(env('STRIPE_SK'));

            $stripePaymentMethod = $stripe->paymentMethods->update(
                $validatedData['id'],
                [
                    'card' => [
                        'exp_month' => $validatedData['expirationMonth'],
                        'exp_year' => $validatedData['expirationYear'],
                    ],
                    'billing_details' => [
                        'address' => [
                            'postal_code' => $validatedData['postalCode']
                        ]
                    ]
                ]
            );



            //
            return [
                'isResultOk' => true,
                'validatedData' => $validatedData,
                'newPayment' => $stripePaymentMethod
            ];
        } catch (Exception $e) {
            return [
                'isResultOk' => false,
                'validatedData' => $validatedData,
                'customErrors' => ["Payment Error" => [$e->getMessage()]]
            ];
        }
    }



    public function save(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'id' => 'nullable|numeric',
            'cardNumber' => 'required|regex:/^[0-9]{16,128}$/',
            'expirationMonth' => 'required|integer|min:1|max:12',
            'expirationYear' => 'required|integer|min:2020|max:2030',
            'cvc' => 'required|string|max:9',
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
                'stripePaymentMethodId' => $stripePaymentMethod->id,
                'newPayment' => $stripePaymentMethod
            ];
        } catch (Exception $e) {
            return [
                'isResultOk' => false,
                'validatedData' => $validatedData,
                'customErrors' => ["Payment Error" => [$e->getMessage()]]
            ];
        }
    }
}
