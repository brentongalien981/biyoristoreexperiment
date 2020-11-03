<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AddressResource;

class CheckoutController extends Controller
{
    public function readCheckoutRequiredData(Request $request)
    {
        $user = Auth::user();

        //
        $stripe = new \Stripe\StripeClient(env('STRIPE_SK'));

        $paymentMethods = $stripe->paymentMethods->all([
            'customer' => $user->stripeCustomer->stripe_customer_id,
            'type' => 'card',
        ]);


        return [
            'message' => 'From CLASS: CheckoutController, METHOD: readCheckoutRequiredData()',
            'objs' => [
                'addresses' => AddressResource::collection($user->addresses),
                'paymentInfos' => $paymentMethods['data'],
            ]
        ];
    }
}
