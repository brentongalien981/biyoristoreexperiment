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
    private static function getOrderAmount($items)
    {

        $orderTotalAmount = 0;

        foreach ($items as $i) {
            $i = json_decode($i);

            $sellerProduct = SellerProductCacheObject::getUpdatedModelCacheObjWithId($i->sellerProductId)->data;
            $regularSellPrice = floatval($sellerProduct->sell_price);
            $discountSellPrice = floatval($sellerProduct->discount_sell_price);

            $purchasePrice = $regularSellPrice;
            if ($discountSellPrice > 0 && $discountSellPrice < $regularSellPrice) {
                $purchasePrice = $discountSellPrice;
            }

            $itemTotalPrice = $purchasePrice * $i->quantity;

            $orderTotalAmount += $itemTotalPrice;
        }

        $orderTotalAmount = $orderTotalAmount * (1 + BmdGlobalConstants::TAX_RATE);

        $orderTotalAmountInCents = round($orderTotalAmount, 2) * 100;
        return $orderTotalAmountInCents;
    }



    public function create(Request $request)
    {
        // BMD-ON-STAGING
        \Stripe\Stripe::setApiKey(env('STRIPE_SK'));


        try {

            BmdAuthProvider::setInstance($request->bmdToken, $request->authProviderId);

            $tempGuestUserId = $request->temporaryGuestUserId;
            $user = null;
            if (BmdAuthProvider::check()) {
                $user = BmdAuthProvider::user();
            }


            // BMD-TODO: Add these as meta-data
            // - charged_subtotal
            // - charged_shipping_fee
            // - charged_tax
            // - estimated-total-delivery-days
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => self::getOrderAmount($request->cartItemsData),
                'currency' => 'usd',
                'customer' => (isset($user) ? $user->stripeCustomer->stripe_customer_id : null),
                'metadata' => [
                    'storeUserId' => (isset($user) ? $user->id : $tempGuestUserId),
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
            // Do this so that when for some reason the customer gets charged for payment and the 
            // app gives an error finalizing the order, you'll be able to track which order items
            // he made through the cart-items and Stripe backend.
            $cacheKey = 'cart?userId=' . (isset($user) ? $user->id : $tempGuestUserId);
            $cartCO = new CartCacheObject($cacheKey);
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
                $cart->stripe_payment_intent_id = $paymentIntent->id;
                $cart->save();

                $cacheCart->id = $cart->id;
            }


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
            $cacheCart->cartItems = $updatedCacheCartItems;
            $cartCO->data = $cacheCart;
            $cartCO->save();


            // BMD-TODO: Edit the Stripe-Payment-Intent-obj. Add the cart and cart-items details as backup record.



            return [
                'isResultOk' => true,
                'clientSecret' => $paymentIntent->client_secret
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }
}
