<?php

namespace App\Http\Controllers;

use App\Http\BmdHelpers\BmdAuthProvider;
use App\StripeCustomer;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StripePaymentMethodController extends Controller
{
    public function delete(Request $r)
    {
        $u = BmdAuthProvider::user();

        $v = $r->validate([
            'paymentMethodId' => 'required'
        ]);


        $stripe = new \Stripe\StripeClient(env('STRIPE_SK'));

        $stripe->paymentMethods->detach(
            $v['paymentMethodId'],
            []
        );


        $resultData = StripeCustomer::clearCachePaymentMethodsWithUser($u);

        return [
            'isResultOk' => true
        ];
    }



    public function update(Request $request)
    {
        $user = BmdAuthProvider::user();

        $validatedData = $request->validate([
            'id' => 'required',
            'expirationMonth' => 'required|integer|min:1|max:12',
            'expirationYear' => 'required|integer|min:2020|max:2030',
            'postalCode' => 'required'
        ]);


        $overallProcessLogs = ['In CLASS: StripePaymentMethodController, METHOD: update()'];


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
            $overallProcessLogs[] = 'updated stripePaymentMethod';


            $resultData = StripeCustomer::clearCachePaymentMethodsWithUser($user);
            $overallProcessLogs = array_merge($overallProcessLogs, $resultData['processLogs']);



            //
            return [
                'isResultOk' => true,
                // 'overallProcessLogs' => $overallProcessLogs,
                'objs' => [
                    'newPayment' => $stripePaymentMethod
                ],
            ];
        } catch (Exception $e) {
            $overallProcessLogs[] = 'caught-custom-error ==> ' . $e->getMessage();

            return [
                'isResultOk' => false,
                'caughtCustomError' => $e->getMessage(),
                // 'overallProcessLogs' => $overallProcessLogs,
            ];
        }
    }



    public function save(Request $request)
    {
        $user = BmdAuthProvider::user();

        $validatedData = $request->validate([
            'id' => 'nullable|numeric',
            'cardNumber' => 'required|regex:/^[0-9]{16,128}$/',
            'expirationMonth' => 'required|integer|min:1|max:12',
            'expirationYear' => 'required|integer|min:2020|max:2030',
            'cvc' => 'required|string|max:9',
            'postalCode' => 'required'
        ]);

        $overallProcessLogs = ['In CLASS: StripePaymentMethodController, METHOD: save()'];


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
            $overallProcessLogs[] = 'created stripePaymentMethod';


            $stripeCustomerId = $user->stripeCustomer->stripe_customer_id;

            $stripe->paymentMethods->attach(
                $stripePaymentMethod->id,
                ['customer' => $stripeCustomerId]
            );
            $overallProcessLogs[] = 'attached stripePaymentMethod to stripe-customer';


            $resultData = StripeCustomer::clearCachePaymentMethodsWithUser($user);
            $overallProcessLogs = array_merge($overallProcessLogs, $resultData['processLogs']);



            return [
                'isResultOk' => true,
                // 'overallProcessLogs' => $overallProcessLogs,
                'objs' => [
                    'stripeCustomerId' => $stripeCustomerId,
                    'stripePaymentMethodId' => $stripePaymentMethod->id,
                    'newPayment' => $stripePaymentMethod
                ],
            ];
        } catch (Exception $e) {
            $overallProcessLogs[] = 'caught-custom-error ==> ' . $e->getMessage();

            return [
                'isResultOk' => false,
                'caughtCustomError' => $e->getMessage(),
                // 'overallProcessLogs' => $overallProcessLogs,
            ];
        }
    }
}
