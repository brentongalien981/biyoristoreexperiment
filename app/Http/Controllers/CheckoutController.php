<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Order;
use App\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AddressResource;
use App\Http\Resources\ProfileResource;
use App\OrderItem;
use Exception;

class CheckoutController extends Controller
{
    public function finalizeOrder(Request $request)
    {

        // Create order record with status "PAID".
        $user = Auth::user();
        $cart = Cart::find($request->cartId);

        $order = new Order();
        $order->user_id = (isset($user) ? $user->id : null);
        $order->stripe_payment_intent_id = $cart->stripe_payment_intent_id;
        $order->payment_info_id = (isset($request->paymentInfoId) ? $request->paymentInfoId : null);
        $order->status_id = OrderStatus::PAID;

        $order->street = $request->street;
        $order->city = $request->city;
        $order->province = $request->province;
        $order->country = $request->country;
        $order->postal_code = $request->postalCode;
        $order->phone = $request->phone;
        $order->email = $request->email;
        $order->save();



        // Create order-items.
        foreach ($cart->cartItems as $i) {
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $i->product_id;
            $orderItem->price = $i->product->price;
            $orderItem->quantity = $i->quantity;
            $orderItem->save();
        }



        // Update cart record as not-active.
        $cart->is_active = 0;
        $cart->save();



        //
        return [
            'isResultOk' => true,
            'message' => 'From CLASS: CheckoutController, METHOD: finalizeOrder()',
            'cart' => $cart,
            'order' => $order
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
