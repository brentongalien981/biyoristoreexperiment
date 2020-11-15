<?php

namespace App\Http\Controllers;

use App\Cart;
use App\CartItem;
use App\Http\Resources\CartResource;
use Error;
use App\Order;
use App\OrderStatus;
use App\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentIntentController extends Controller
{
    private static function getOrderAmount($items)
    {

        $orderTotalAmount = 0;
        $tax = 0.13;

        foreach ($items as $i) {
            $i = json_decode($i);
            $product = Product::find($i->productId);
            $quantity = $i->quantity;
            $itemTotalPrice = $product->price * $quantity;

            $orderTotalAmount += $itemTotalPrice;
        }

        $orderTotalAmount = $orderTotalAmount * (1 + $tax);

        $orderTotalAmountInCents = round($orderTotalAmount, 2) * 100;
        return $orderTotalAmountInCents;
    }



    // // TODO:LATER Create order record with status "waiting-for-payment".
    // $user = Auth::user();

    // $order = new Order();
    // $order->user_id = (isset($user) ? $user->id : null);
    // $order->stripe_payment_intent_id = $paymentIntent->id;
    // $order->payment_info_id = (isset($request->paymentInfoId) ? $request->paymentInfoId : null);
    // $order->status_id = OrderStatus::WAITING_FOR_PAYMENT;

    // $order->street = $request->street;
    // $order->city = $request->city;
    // $order->province = $request->province;
    // $order->country = $request->country;
    // $order->postal_code = $request->postalCode;
    // $order->phone = $request->phone;
    // $order->email = $request->email;
    // $order->save();





    public function create(Request $request)
    {

        // TODO:LATER: Validate shipping info when you implement the shipping feature.


        // This is your real test secret API key.
        \Stripe\Stripe::setApiKey(env('STRIPE_SK'));


        try {

            //
            $user = Auth::user();

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => self::getOrderAmount($request->cartItemsData),
                'currency' => 'usd',
                'customer' => (isset($user) ? $user->stripeCustomer->stripe_customer_id : null),
                'metadata' => [
                    'storeUserId' => (isset($user) ? $user->id : null),
                    'firstName' => $request->firstName,
                    'lastName' => $request->lastName,
                    'phoneNumber' => $request->phone,
                    'email' => $request->email,
                    'street' => $request->street,
                    'city' => $request->city,
                    'province' => $request->province,
                    'country' => $request->country,
                    'postalCode' => $request->postalCode,
                ]
            ]);



            // Create / update cart record.
            $cart = null;
            if (isset($request->cartId) && $request->cartId != 0) {
                $cart = Cart::find($request->cartId);
            } else {
                $cart = new Cart();
            }
            $cart->stripe_payment_intent_id = $paymentIntent->id;
            $cart->save();


            // Create / update cart-items
            foreach ($request->cartItemsData as $i) {
                $i = json_decode($i);

                $updatedCartItem = null;
                if (isset($i->id)) {
                    $updatedCartItem = CartItem::find($i->id);
                } else {
                    $updatedCartItem = new CartItem();
                }
                $updatedCartItem->cart_id = $cart->id;
                $updatedCartItem->product_id = $i->productId;
                $updatedCartItem->quantity = $i->quantity;
                $updatedCartItem->save();
            }



            //
            return [
                'clientSecret' => $paymentIntent->client_secret,
                'cart' => new CartResource($cart),
                'street' => $request->street,
                'cartItemsData' => $request->cartItemsData,
            ];
        } catch (Exception $e) {
            throw $e;
            // return ['customError' => $e->getMessage()];
        }
    }
}
