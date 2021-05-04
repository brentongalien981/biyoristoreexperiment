<?php

namespace App\Http\Controllers;

use Error;
use App\Cart;
use App\Order;
use Exception;
use App\Product;
use App\CartItem;
use App\Http\BmdCacheObjects\CartCacheObject;
use App\Http\BmdCacheObjects\SellerProductCacheObject;
use App\OrderStatus;
use Illuminate\Http\Request;
use App\Http\Resources\CartResource;
use Illuminate\Support\Facades\Auth;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\MyConstants\BmdGlobalConstants;

class PaymentIntentController extends Controller
{
    private static function getOrderSubtotal($items)
    {

        $orderSubtotal = 0;

        foreach ($items as $i) {
            $i = json_decode($i);

            $sellerProductCO = SellerProductCacheObject::getUpdatedModelCacheObjWithId($i->sellerProductId);
            $purchasePrice = $sellerProductCO->getSellerProductPurchasePrice();

            $itemTotalPrice = $purchasePrice * $i->quantity;

            $orderSubtotal += $itemTotalPrice;
        }

        return $orderSubtotal;
    }



    public function create(Request $request)
    {
        // BMD-ON-STAGING
        \Stripe\Stripe::setApiKey(env('STRIPE_SK'));


        try {

            BmdAuthProvider::setInstance($request->bmdToken, $request->authProviderId);

            $userId = $request->temporaryGuestUserId;
            $user = null;
            if (BmdAuthProvider::check()) {
                $user = BmdAuthProvider::user();
                $userId = $user->id;
            }

            $cacheKey = 'cart?userId=' . (isset($user) ? $user->id : $userId);
            $cartCO = new CartCacheObject($cacheKey);


            // order-meta-data
            $chargedSubtotal = $cartCO->getOrderSubtotal();
            $chargedShippingFee = $request->shipmentRateAmount;
            $chargedTax = ($chargedSubtotal + $chargedShippingFee) * BmdGlobalConstants::TAX_RATE;
            $chargedTotal = $chargedSubtotal + $chargedShippingFee + $chargedTax;
            $chargedTotalInCents = round($chargedTotal, 2) * 100;
            $projectedTotalDeliveryDays = $request->projectedTotalDeliveryDays;


            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $chargedTotalInCents,
                'currency' => 'usd',
                'customer' => (isset($user) ? $user->stripeCustomer->stripe_customer_id : null),
                'metadata' => [
                    'storeUserId' => $userId,
                    'firstName' => $request->firstName,
                    'lastName' => $request->lastName,
                    'phoneNumber' => $request->phone,
                    'email' => $request->email,
                    'street' => $request->street,
                    'city' => $request->city,
                    'province' => $request->province,
                    'country' => $request->country,
                    'postalCode' => $request->postalCode,

                    'chargedSubtotal' => $chargedSubtotal,
                    'chargedShippingFee' => $chargedShippingFee,
                    'chargedTax' => $chargedTax,
                    'chargedTotal' => $chargedTotal,
                    'projectedTotalDeliveryDays' => $projectedTotalDeliveryDays

                    // BMD-TODO: Add fields:
                    // - earliest_delivery_date
                    // - latest_delivery_date
                ]
            ]);



            // Create / update cart record.
            // Do this so that when for some reason the customer gets charged for payment and the 
            // app gives an error finalizing the order, you'll be able to track which order items
            // he made through the cart-items and Stripe backend.
            $cacheCart = $cartCO->data;
            $cart = null;
            $shouldCreateNewCart = true;

            if (isset($cacheCart->id) && $cacheCart->id != 0) {
                $cart = Cart::find($cacheCart->id);
                if ($cart->is_active) {
                    $shouldCreateNewCart = false;
                }
            }
            if ($shouldCreateNewCart) {
                $cart = new Cart();
                $cart->user_id = (isset($user) ? $user->id : null);
            }

            // Update the db-cart.
            $cart->stripe_payment_intent_id = $paymentIntent->id;
            $cart->save();


            $cacheCart->id = $cart->id;


            // Create / update cart-items
            $updatedCacheCartItems = [];
            foreach ($cacheCart->cartItems as $cci) {

                $updatedCartItem = null;

                if (isset($cci->id)) {
                    $updatedCartItem = CartItem::find($cci->id);
                } else {
                    $updatedCartItem = new CartItem();
                }

                $updatedCartItem->cart_id = $cacheCart->id;
                $updatedCartItem->product_id = $cci->productId;
                $updatedCartItem->product_seller_id = $cci->sellerProductId;
                $updatedCartItem->size_availability_id = $cci->sizeAvailabilityId;
                $updatedCartItem->quantity = $cci->quantity;
                $updatedCartItem->save();

                $cci->id = $updatedCartItem->id;
                $cci->cart_id = $cacheCart->id;

                $updatedCacheCartItems[] = $cci;
            }

            // Update cache-cart.
            $cacheCart->paymentIntentId = $paymentIntent->id;
            $cacheCart->cartItems = $updatedCacheCartItems;
            $cartCO->data = $cacheCart;
            $cartCO->save();


            // BMD-TODO: On DEV-ITER-004 / FEAT: Checkout: Edit the Stripe-Payment-Intent-obj. Add the cart and cart-items details as backup record.



            return [
                'isResultOk' => true,
                'clientSecret' => $paymentIntent->client_secret
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }
}
