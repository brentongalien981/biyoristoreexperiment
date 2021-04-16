<?php

namespace App\Http\Controllers;

use Error;
use App\Cart;
use App\Order;
use Exception;
use App\Product;
use App\CartItem;
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

            $sellerProduct= SellerProductCacheObject::getUpdatedModelCacheObjWithId($i->sellerProductId)->data;
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

        // TODO:LATER: Validate shipping info when you implement the shipping feature.


        // This is your real test secret API key.
        \Stripe\Stripe::setApiKey(env('STRIPE_SK'));


        try {

            BmdAuthProvider::setInstance($request->bmdToken, $request->authProviderId);

            $user = null;
            if (BmdAuthProvider::check()) {
                $user = BmdAuthProvider::user();
            }

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


            //bmd-ish: Maybe you don't have to do this?
            //bmd-ish: Save the payment-intent-id to cart-cache. You'll need it for finalizing order.
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
