<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Order;
use Exception;
use App\Product;
use App\CartItem;
use App\OrderItem;
use App\OrderStatus;
use App\PaymentStatus;
use App\SizeAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\AddressResource;
use App\Http\Resources\ProfileResource;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\BmdCacheObjects\CartCacheObject;
use App\Http\BmdCacheObjects\ProductResourceCacheObject;
use App\Http\BmdCacheObjects\ProfileResourceCacheObject;
use App\Http\BmdCacheObjects\UserStripePaymentMethodsCacheObject;
use App\Http\BmdCacheObjects\AddressResourceCollectionCacheObject;

class CheckoutController extends Controller
{
    public function doOrderInventoryChecks(Request $r)
    {

        BmdAuthProvider::setInstance($r->bmdToken, $r->authProviderId);

        $userId = $r->temporaryGuestUserId;
        if (BmdAuthProvider::check()) {
            $user = BmdAuthProvider::user();
            $userId = $user->id;
        }


        /** Check if the inventory quantities can supply the order-items quantities. */
        $cacheCO = new CartCacheObject('cart?userId=' . $userId);
        $cartItems = $cacheCO->data->cartItems;

        $failedCheckObjs = [];
        foreach ($cartItems as $ci) {

            $sizeAvailabilityObj = SizeAvailability::find($ci->sizeAvailabilityId);

            if ($ci->quantity > $sizeAvailabilityObj->quantity) {
                // Append a failed-check-msg.
                $productResourceCO = ProductResourceCacheObject::getUpdatedResourceCacheObjWithId($ci->productId);
                $failedCheckObjs[] = [
                    'productName' => $productResourceCO->data->name,
                    'productInventoryQuantity' => $sizeAvailabilityObj->quantity,
                    'size' => $sizeAvailabilityObj->size,
                    'orderItemQuantity' => $ci->quantity
                ];
                
            }
        }


        return [
            'isResultOk' => count($failedCheckObjs) > 0 ? false : true,
            'objs' => [
                'orderItemExceedInventoryQuantityFailedCheckObjs' => $failedCheckObjs
            ]
        ];

    }



    private static function checkCartItemExistence($items)
    {
        foreach ($items as $i) {
            $i = json_decode($i);

            $product = Product::find($i->productId);
            if (isset($product)) {
                return true;
            }

            return false;
        }

        return false;
    }



    public function finalizeOrderWithPredefinedPayment(Request $request)
    {

        $user = Auth::user();
        $paymentProcessStatusCode = PaymentStatus::WAITING_FOR_PAYMENT;
        $orderProcessStatusCode = OrderStatus::getIdByName('INVALID_CART');
        $order = null;
        $cart = null;
        $isResultOk = false;
        $customError = null;
        $customeMsgs = ['invalid cart'];


        try {

            // validate payment-method
            $orderProcessStatusCode = OrderStatus::getIdByName('INVALID_PAYMENT_METHOD');
            $stripe = new \Stripe\StripeClient(env('STRIPE_SK'));

            $paymentMethod = $stripe->paymentMethods->retrieve(
                $request->paymentMethodId,
                []
            );

            $customeMsgs[] = 'paymentMethod->customer ==> ' . $paymentMethod->customer;
            $customeMsgs[] = 'user->customerId ==> ' . $user->stripeCustomer->stripe_customer_id;
            if ($paymentMethod->customer === $user->stripeCustomer->stripe_customer_id) {
                $customeMsgs[] = 'payment method is valid';
                $orderProcessStatusCode = OrderStatus::getIdByName('WAITING_FOR_PAYMENT');
            } else {
                $customeMsgs[] = 'invalid payment method';
                throw new Exception("INVALID_PAYMENT_METHOD");
            }



            // create payment-intent
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => Order::getOrderAmountInCents($request->cartItemsInfo),
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'customer' => $user->stripeCustomer->stripe_customer_id,
                'payment_method' => $paymentMethod->id,
                'metadata' => [
                    'storeUserId' => $user->id,
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

            $customeMsgs[] = 'payment-intent created';



            // create cart
            $cart = new Cart();
            $cart->user_id = $user->id;
            $cart->stripe_payment_intent_id = $paymentIntent->id;
            $cart->save();
            $orderProcessStatusCode = OrderStatus::getIdByName('VALID_CART');

            $customeMsgs[] = 'cart created';




            // check pseudo-cart-items existence
            if (!self::checkCartItemExistence($request->cartItemsInfo)) {
                $orderProcessStatusCode = OrderStatus::getIdByName('CART_HAS_NO_ITEM');
                throw new Exception("CART_HAS_NO_ITEM");
            }
            $orderProcessStatusCode = OrderStatus::getIdByName('CART_HAS_ITEM');
            $customeMsgs[] = 'cart has at least one item';



            // create and associate cart-items
            foreach ($request->cartItemsInfo as $i) {
                $i = json_decode($i);
                $cartItem = new CartItem();
                $cartItem->cart_id = $cart->id;
                $cartItem->product_id = $i->productId;
                $cartItem->quantity = $i->quantity;
                $cartItem->save();
            }

            $customeMsgs[] = 'cart-items created';



            // charge customer
            $stripe->paymentIntents->confirm(
                $paymentIntent->id
            );

            $paymentProcessStatusCode = PaymentStatus::PAYMENT_METHOD_CHARGED;
            $orderProcessStatusCode = OrderStatus::getIdByName('PAYMENT_METHOD_CHARGED');
            $customeMsgs[] = 'payment-method charged';



            // set cart to not active
            $cart->is_active = 0;
            $cart->save();

            $orderProcessStatusCode = OrderStatus::getIdByName('CART_CHECKEDOUT_OK');
            $customeMsgs[] = 'cart checkedout ok';



            // create order-record with status "order-created"
            $order = new Order();
            $order->user_id = $user->id;
            $order->stripe_payment_intent_id = $cart->stripe_payment_intent_id;
            // $order->payment_info_id = $paymentMethod->id;
            $order->status_id = OrderStatus::getIdByName('ORDER_CREATED');

            $order->street = $request->street;
            $order->city = $request->city;
            $order->province = $request->province;
            $order->country = $request->country;
            $order->postal_code = $request->postalCode;
            $order->phone = $request->phone;
            $order->email = $request->email;
            $order->save();

            $orderProcessStatusCode = OrderStatus::getIdByName('ORDER_CREATED');
            $customeMsgs[] = 'order created';



            // create order-items
            foreach ($cart->cartItems as $i) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $i->product_id;
                $orderItem->price = $i->product->price;
                $orderItem->quantity = $i->quantity;
                $orderItem->save();
            }

            $orderProcessStatusCode = OrderStatus::getIdByName('ORDER_ITEMS_CREATED');
            $customeMsgs[] = 'order-items created';



            //
            $isResultOk = true;
        } catch (Exception $e) {
            $customeMsgs[] = 'inside CATCH clause';
            $customError = $e->getMessage();
        } finally {

            $customeMsgs[] = 'inside FINALLY clause';

            return [
                'isResultOk' => $isResultOk,
                'message' => 'From CLASS: CheckoutController, METHOD: finalizeOrderWithPredefinedPayment()',
                'paymentProcessStatusCode' => $paymentProcessStatusCode,
                'orderProcessStatusCode' => $orderProcessStatusCode,
                'customeMsgs' => $customeMsgs,
                'customeError' => $customError,
                'order' => $order,
                'cart' => $cart,
            ];
        }
    }



    public function finalizeOrder(Request $request)
    {
        BmdAuthProvider::setInstance($request->bmdToken, $request->authProviderId);

        $userId = $request->temporaryGuestUserId;
        $user = null;
        if (BmdAuthProvider::check()) {
            $user = BmdAuthProvider::user();
            $userId = $user->id;
        }

        $paymentProcessStatusCode = PaymentStatus::PAYMENT_METHOD_CHARGED;
        $orderProcessStatusCode = OrderStatus::getIdByName('INVALID_CART');

        // BMD-ISH
        // Create order record with status "PAID".
        $cart = Cart::find($request->cartId);


        try {

            //
            if (!isset($cart)) {
                throw new Exception("Invalid cart.");
            } else {
                $orderProcessStatusCode = OrderStatus::getIdByName('VALID_CART');
            }


            //
            if (count($cart->cartItems) == 0) {
                $orderProcessStatusCode = OrderStatus::getIdByName('CART_HAS_NO_ITEM');
                throw new Exception("Cart has no item.");
            } else {
                $orderProcessStatusCode = OrderStatus::getIdByName('CART_HAS_ITEM');
            }


            // Update cart record as not-active.
            $cart->is_active = 0;
            $cart->save();

            $orderProcessStatusCode = OrderStatus::getIdByName('CART_CHECKEDOUT_OK');


            // bmd-todo: Make the order id a UUID type.
            $order = new Order();
            $order->user_id = (isset($user) ? $user->id : null);
            $order->stripe_payment_intent_id = $cart->stripe_payment_intent_id;
            $order->payment_info_id = (isset($request->paymentInfoId) ? $request->paymentInfoId : null);
            $order->status_id = OrderStatus::getIdByName('PAYMENT_METHOD_CHARGED');

            $order->street = $request->street;
            $order->city = $request->city;
            $order->province = $request->province;
            $order->country = $request->country;
            $order->postal_code = $request->postalCode;
            $order->phone = $request->phone;
            $order->email = $request->email;
            $order->save();

            $orderProcessStatusCode = OrderStatus::getIdByName('ORDER_CREATED');



            // Create order-items.
            foreach ($cart->cartItems as $i) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $i->product_id;
                $orderItem->price = $i->product->price; // BMD-TODO: Edit this using seller-product data.
                $orderItem->quantity = $i->quantity;
                $orderItem->save();
            }

            $orderProcessStatusCode = OrderStatus::getIdByName('ORDER_ITEMS_CREATED');



            //
            return [
                'isResultOk' => true,
                'paymentProcessStatusCode' => $paymentProcessStatusCode,
                'orderProcessStatusCode' => $orderProcessStatusCode,
                'order' => $order
            ];
        } catch (Exception $e) {
            return [
                'isResultOk' => false,
                'paymentProcessStatusCode' => $paymentProcessStatusCode,
                'orderProcessStatusCode' => $orderProcessStatusCode,
                'customError' => $e->getMessage(),
                // 'order' => $order // TODO:LATER: Depending on the orderProcessStatusCode, decide whether or not to include this.
            ];
        }
    }



    public function readCheckoutRequiredData(Request $request)
    {
        $user = BmdAuthProvider::user();

        $userPaymentMethodsCO = new UserStripePaymentMethodsCacheObject('userPaymentMethods?userId=' . $user->id);
        $userPaymentMethodsCO = $userPaymentMethodsCO->getMyRefreshedVersion();
        $profileResourceCO = ProfileResourceCacheObject::getUpdatedResourceCacheObjWithId($user->profile->id);

        $foreignKeyId = $user->id;
        $userAddressCollectionCO = AddressResourceCollectionCacheObject::getUpdatedCollection($foreignKeyId);


        return [
            'message' => 'From CLASS: CheckoutController, METHOD: readCheckoutRequiredData()',
            'objs' => [
                'profile' => $profileResourceCO->data ?? [],
                'addresses' => $userAddressCollectionCO->data ?? [],
                'paymentInfos' => $userPaymentMethodsCO->data ?? [],
            ]
        ];
    }
}
