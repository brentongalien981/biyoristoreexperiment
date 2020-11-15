<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AddressResource;
use App\Http\Resources\ProfileResource;

class CheckoutController extends Controller
{
    public function finalizeOrder(Request $request)
    {
        return [
            'isResultOk' => 'PENDING',
            'message' => 'From CLASS: CheckoutController, METHOD: finalizeOrder()',
            'cartId' => $request->cartId,
            'street' => $request->street,
            'country' => $request->country,
            'phone' => $request->phone,
            'email' => $request->email
        ];
    }



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
                'profile' => new ProfileResource($user->profile),
                'addresses' => AddressResource::collection($user->addresses),
                'paymentInfos' => $paymentMethods['data'],
            ]
        ];
    }
}
