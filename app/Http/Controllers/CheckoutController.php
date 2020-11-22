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
use App\PaymentStatus;
use Exception;

class CheckoutController extends Controller
{
    public function finalizeOrder(Request $request)
    {
        //
        $user = Auth::user();
        $paymentProcessStatusCode = PaymentStatus::PAYMENT_METHOD_CHARGED;
        $orderProcessStatusCode = OrderStatus::INVALID_CART;


        // Create order record with status "PAID".
        $cart = Cart::find($request->cartId);


        try {

            //
            if (!isset($cart)) {
                throw new Exception("Invalid cart.");
            } else {
                $orderProcessStatusCode = OrderStatus::VALID_CART;
            }


            //
            if (count($cart->cartItems) == 0) {
                $orderProcessStatusCode = OrderStatus::CART_HAS_NO_ITEM;
                throw new Exception("Cart has no item.");
            } else {
                $orderProcessStatusCode = OrderStatus::CART_HAS_ITEM;
            }


            // Update cart record as not-active.
            $cart->is_active = 0;
            $cart->save();

            $orderProcessStatusCode = OrderStatus::CART_CHECKEDOUT_OK;


            //
            $order = new Order();
            $order->user_id = (isset($user) ? $user->id : null);
            $order->stripe_payment_intent_id = $cart->stripe_payment_intent_id;
            $order->payment_info_id = (isset($request->paymentInfoId) ? $request->paymentInfoId : null);
            $order->status_id = OrderStatus::PAYMENT_METHOD_CHARGED;

            $order->street = $request->street;
            $order->city = $request->city;
            $order->province = $request->province;
            $order->country = $request->country;
            $order->postal_code = $request->postalCode;
            $order->phone = $request->phone;
            $order->email = $request->email;
            $order->save();

            $orderProcessStatusCode = OrderStatus::ORDER_CREATED;



            // Create order-items.
            foreach ($cart->cartItems as $i) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $i->product_id;
                $orderItem->price = $i->product->price;
                $orderItem->quantity = $i->quantity;
                $orderItem->save();
            }

            $orderProcessStatusCode = OrderStatus::ORDER_ITEMS_CREATED;



            //
            return [
                'isResultOk' => true,
                'message' => 'From CLASS: CheckoutController, METHOD: finalizeOrder()',
                'cart' => $cart,
                'paymentProcessStatusCode' => $paymentProcessStatusCode,
                'orderProcessStatusCode' => $orderProcessStatusCode,
                'order' => $order
            ];
        } catch (Exception $e) {
            return [
                'isResultOk' => false,
                'message' => 'From CLASS: CheckoutController, METHOD: finalizeOrder()',
                'cart' => $cart,
                'paymentProcessStatusCode' => $paymentProcessStatusCode,
                'orderProcessStatusCode' => $orderProcessStatusCode,
                'customError' => $e->getMessage(),
                'order' => $order
            ];
        }
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
