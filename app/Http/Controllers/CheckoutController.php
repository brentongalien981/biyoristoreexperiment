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
use App\StripeCustomer;
use App\IncompleteOrder;
use App\SizeAvailability;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\EmailUserOrderDetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\AddressResource;
use App\Http\Resources\ProfileResource;
use App\MyConstants\BmdGlobalConstants;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\BmdCacheObjects\CartCacheObject;
use App\Http\BmdCacheObjects\OrderStatusCacheObject;
use App\Http\BmdCacheObjects\ExchangeRateCacheObject;
use App\Http\BmdCacheObjects\SellerProductCacheObject;
use App\Http\BmdCacheObjects\ProductResourceCacheObject;
use App\Http\BmdCacheObjects\ProfileResourceCacheObject;
use App\Http\BmdCacheObjects\InventoryOrderLimitsCacheObject;
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



    private function validateStripePaymentMethod(&$entireProcessData)
    {

        $paymentMethod = $entireProcessData['stripeObj']->paymentMethods->retrieve(
            $entireProcessData['request']->paymentMethodId,
            []
        );

        $entireProcessData['stripePaymentMethod'] = $paymentMethod;


        if ($paymentMethod->customer === $entireProcessData['user']->stripeCustomer->stripe_customer_id) {
            $status = OrderStatusCacheObject::getDataByName('PAYMENT_METHOD_VALIDATED');
            $entireProcessData['resultCode'] = $status->code;
            $entireProcessData['entireProcessLogs'][] = $status->readable_name;
        } else {
            $status = OrderStatusCacheObject::getDataByName('INVALID_PAYMENT_METHOD');
            $entireProcessData['resultCode'] = $status->code;
            $entireProcessData['entireProcessLogs'][] = $status->readable_name;
            throw new Exception($status->readable_name);
        }
    }



    private function createStripePaymentIntent(&$entireProcessData)
    {
        $r = $entireProcessData['request'];

        $chargedSubtotal = $entireProcessData['cartCO']->getOrderSubtotal();

        $exchangeRate = ExchangeRateCacheObject::getConversionRate('CAD', 'USD')->rate;
        $chargedShippingFee = floatval($r->shipmentRateAmount) * floatval($exchangeRate);
        $chargedShippingFee = round($chargedShippingFee, 2);

        $chargedTax = ($chargedSubtotal + $chargedShippingFee) * BmdGlobalConstants::TAX_RATE;
        $chargedTax = round($chargedTax, 2);

        $chargedTotal = $chargedSubtotal + $chargedShippingFee + $chargedTax;
        $chargedTotal = round($chargedTotal, 2);

        $chargedTotalInCents = $chargedTotal * 100;

        $projectedTotalDeliveryDays = $r->projectedTotalDeliveryDays;
        $projectedShortestDeliveryDays = $projectedTotalDeliveryDays - BmdGlobalConstants::PAYMENT_TO_FUNDS_PERIOD - BmdGlobalConstants::ORDER_PROCESSING_PERIOD;

        $earliestDeliveryDate = Order::getDeliveryDate($projectedShortestDeliveryDays);
        $latestDeliveryDate = Order::getDeliveryDate($projectedTotalDeliveryDays);

        $paymentIntent = $entireProcessData['stripeObj']->paymentIntents->create([
            'amount' => $chargedTotalInCents,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
            'customer' => $entireProcessData['user']->stripeCustomer->stripe_customer_id,
            'payment_method' => $entireProcessData['stripePaymentMethod']->id,
            'metadata' => [
                'storeUserId' => $entireProcessData['user']->id,
                'firstName' => $r->firstName,
                'lastName' => $r->lastName,
                'phoneNumber' => $r->phone,
                'email' => $r->email,
                'street' => $r->street,
                'city' => $r->city,
                'province' => $r->province,
                'country' => $r->country,
                'postalCode' => $r->postalCode,

                'chargedSubtotal' => $chargedSubtotal,
                'chargedShippingFee' => $chargedShippingFee,
                'chargedTax' => $chargedTax,
                'chargedTotal' => $chargedTotal,

                'projectedTotalDeliveryDays' => $projectedTotalDeliveryDays,
                'earliestDeliveryDate' => $earliestDeliveryDate,
                'latestDeliveryDate' => $latestDeliveryDate


                // BMD-TODO: on DEV-ITER-004: Include cart and cart-items data as well.
            ]
        ]);

        $entireProcessData['stripePaymentIntent'] = $paymentIntent;
        $entireProcessData['stripePaymentIntentId'] = $paymentIntent->id;


        $status = OrderStatusCacheObject::getDataByName('STRIPE_PAYMENT_INTENT_CREATED');
        $entireProcessData['resultCode'] = $status->code;
        $entireProcessData['entireProcessLogs'][] = $status->readable_name;
    }



    private function updateOrCreateCart(&$entireProcessData)
    {
        // Db-cart part.
        $cartCO = $entireProcessData['cartCO'];
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
        }

        $cart->user_id = $entireProcessData['userId'];
        $cart->stripe_payment_intent_id = $entireProcessData['stripePaymentIntent']->id;
        $cart->save();

        $status = OrderStatusCacheObject::getDataByName('DB_CART_CREATED');
        $entireProcessData['resultCode'] = $status->code;
        $entireProcessData['entireProcessLogs'][] = $status->readable_name;


        // Cache-cart part.
        $cacheCart->id = $cart->id;
        $cacheCart->paymentIntentId = $entireProcessData['stripePaymentIntent']->id;
        $cartCO->data = $cacheCart;
        $cartCO->save();

        $entireProcessData['cartId'] = $cart->id;
        $entireProcessData['cartObj'] = $cart;
        $entireProcessData['cartCO'] = $cartCO;


        $status = OrderStatusCacheObject::getDataByName('CACHE_CART_UPDATED_TO_LATEST_VERSION');
        $entireProcessData['resultCode'] = $status->code;
        $entireProcessData['entireProcessLogs'][] = $status->readable_name;
    }



    private function checkCartItems(&$entireProcessData)
    {
        if (count($entireProcessData['cartCO']->data->cartItems) == 0) {
            $status = OrderStatusCacheObject::getDataByName('CART_HAS_NO_ITEM');
            $entireProcessData['resultCode'] = $status->code;
            $entireProcessData['entireProcessLogs'][] = $status->readable_name;
            throw new Exception($status->readable_name);
        }
    }



    private function updateCartItems(&$entireProcessData)
    {
        // Delete all associated cart-items from cart.
        $cartId = $entireProcessData['cartId'];
        DB::table('cart_items')->where('cart_id', $cartId)->delete();


        // Create and associate cart-items to db-cart.
        foreach ($entireProcessData['cartCO']->data->cartItems as $i) {
            $cartItem = new CartItem();
            $cartItem->cart_id = $cartId;
            $cartItem->product_id = $i->productId;
            $cartItem->quantity = $i->quantity;
            $cartItem->product_seller_id = $i->sellerProductId;
            $cartItem->size_availability_id = $i->sizeAvailabilityId;
            $cartItem->save();
        }


        $status = OrderStatusCacheObject::getDataByName('DB_CART_ITEMS_CREATED');
        $entireProcessData['resultCode'] = $status->code;
        $entireProcessData['entireProcessLogs'][] = $status->readable_name;
    }



    private function confirmStripePaymentIntent(&$entireProcessData)
    {
        $entireProcessData['stripeObj']->paymentIntents->confirm(
            $entireProcessData['stripePaymentIntent']->id
        );


        $entireProcessData['hasUserBeenCharged'] = true;

        $status = OrderStatusCacheObject::getDataByName('PAYMENT_METHOD_CHARGED');
        $entireProcessData['resultCode'] = $status->code;
        $entireProcessData['entireProcessLogs'][] = $status->readable_name;
    }



    public function finalizeOrderWithPredefinedPayment(Request $request)
    {

        // Initialize entire-process-data (params and data).
        $user = BmdAuthProvider::user();
        $cacheCartKey = 'cart?userId=' . $user->id;
        $entireProcessData = [
            'hasUserBeenCharged' => false,
            'cacheCartHasBeenReset' => false,
            'hasOrderBeenCreated' => false,
            'userId' => $user->id,
            'user' => $user,
            'cartObj' => null,
            'cartId' => 0,
            'cartCO' => new CartCacheObject($cacheCartKey),
            'newCartCO' => null,
            'stripeObj' => new \Stripe\StripeClient(env('STRIPE_SK')), // BMD-ON-STAGING
            'stripePaymentMethod' => null,
            'stripePaymentIntent' => null,
            'stripePaymentIntentId' => null,
            'entireProcessLogs' => [
                OrderStatusCacheObject::getReadableNameByName('START_OF_FINALIZING_ORDER_WITH_PREDEFINED_PAYMENT')
            ],
            'resultCode' => OrderStatusCacheObject::getCodeByName('START_OF_FINALIZING_ORDER_WITH_PREDEFINED_PAYMENT'),
            'orderId' => 0,
            'order' => null,
            'request' => $request
        ];


        try {

            // BMD-TODO: On DEV-ITER-004: FEAT: Checkout
            // Validate the other request-params.


            $this->checkCartItems($entireProcessData);
            $this->validateStripePaymentMethod($entireProcessData);
            $this->createStripePaymentIntent($entireProcessData);
            $this->updateOrCreateCart($entireProcessData);
            $this->updateCartItems($entireProcessData);
            $this->confirmStripePaymentIntent($entireProcessData);

            $this->updateInventoryQuantities($entireProcessData);
            $this->updateInventoryOrderLimits($entireProcessData);

            $this->createOrder($entireProcessData);

            EmailUserOrderDetails::dispatch($entireProcessData['order']->id)->onQueue(BmdGlobalConstants::QUEUE_FOR_EMAILING_ORDER_DETAILS);
        } catch (Exception $e) {

            $entireProcessData['exception'] = $e;
            $this->handleEntireProcessException($entireProcessData);
        }


        // Make sure to reset cache-cart if user has been charged.
        $updatedCart = $entireProcessData['cartCO']->data;

        if ($entireProcessData['hasUserBeenCharged']) {

            $this->deactivateDbCart($entireProcessData);
            $this->resetCacheCart($entireProcessData);

            $updatedCart = $entireProcessData['newCartCO']->data;

            if ($entireProcessData['hasOrderBeenCreated']) {
                $this->updateOrderStatus($entireProcessData);
            } else {
                $this->createIncompleteOrderRecord($entireProcessData);
            }
        }


        return [
            'isResultOk' => $entireProcessData['hasUserBeenCharged'],
            'objs' => [
                'paymentProcessStatusCode' => $entireProcessData['hasUserBeenCharged'] ? OrderStatusCacheObject::getCodeByName('PAYMENT_METHOD_CHARGED') : OrderStatusCacheObject::getCodeByName('PAYMENT_METHOD_NOT_CHARGED'),
                'orderProcessStatusCode' => $entireProcessData['resultCode'],
                'orderId' => $entireProcessData['orderId'],
                'updatedCart' => $updatedCart
            ],
            // BMD-FOR-DEBUG
            // BMD-ON-STAGING: Comment-out
            'entireProcessLogs' => $entireProcessData['entireProcessLogs'],
        ];
    }



    /**
     * Check cart validity.
     * Check if cart has items.
     *
     * @param [type] $params
     * @return void
     */
    private function checkCartCache(&$params)
    {
        $cartCO = new CartCacheObject('cart?userId=' . $params['userId']);
        $params['cartCO'] = $cartCO;
        $params['cartObj'] = Cart::find($cartCO->data->id);
        $params['cartId'] = $cartCO->data->id;
        $params['stripePaymentIntentId'] = $cartCO->data->paymentIntentId;

        if (!isset($cartCO->data->id) || $cartCO->data->id == 0 || !isset($cartCO->data->cartItems)) {
            $status = OrderStatusCacheObject::getDataByName('INVALID_CART');
            $params['resultCode'] = $status->code;
            $params['entireProcessLogs'][] = $status->readable_name;
            throw new Exception($status->readable_name);
        } else {
            $status = OrderStatusCacheObject::getDataByName('VALID_CART');
            $params['resultCode'] = $status->code;
            $params['entireProcessLogs'][] = $status->readable_name;
        }


        if (count($cartCO->data->cartItems) == 0) {

            $status = OrderStatusCacheObject::getDataByName('CART_HAS_NO_ITEM');
            $params['resultCode'] = $status->code;
            $params['entireProcessLogs'][] = $status->readable_name;
            throw new Exception($status->readable_name);
        } else {
            $status = OrderStatusCacheObject::getDataByName('CART_HAS_ITEM');
            $params['resultCode'] = $status->code;
            $params['entireProcessLogs'][] = $status->readable_name;
        }
    }



    private function deactivateDbCart(&$params)
    {
        $cart = $params['cartObj'];

        if (isset($cart)) {

            $cart->is_active = 0;
            $cart->save();

            $params['cartObj'] = $cart;

            $status = OrderStatusCacheObject::getDataByName('INVALID_CART');
            $params['resultCode'] = $status->code;
            $params['entireProcessLogs'][] = $status->readable_name;

            return;
        }


        $status = OrderStatusCacheObject::getDataByName('CART_CHECKEDOUT_OK');
        $params['resultCode'] = $status->code;
        $params['entireProcessLogs'][] = $status->readable_name;
    }



    private function retrieveStripePaymentIntent($paymentIntentId)
    {
        // BMD-ON-STAGING
        $stripe = new \Stripe\StripeClient(env('STRIPE_SK'));
        return $stripe->paymentIntents->retrieve(
            $paymentIntentId,
            []
        );
    }



    private function createOrder(&$params)
    {
        $r = $params['request'];
        $spi = $params['stripePaymentIntent'] ?? $this->retrieveStripePaymentIntent($params['cartCO']->data->paymentIntentId);
        $u = $params['user'];
        $manuallyGeneratedOrderId = Str::uuid()->toString();

        $order = new Order();
        $order->id = $manuallyGeneratedOrderId;
        $order->user_id = (isset($u) ? $u->id : null);
        $order->cart_id = $params['cartId'];
        $order->stripe_payment_intent_id = $params['cartCO']->data->paymentIntentId;

        $order->first_name = $r->firstName;
        $order->last_name = $r->lastName;
        $order->street = $r->street;
        $order->city = $r->city;
        $order->province = $r->province;
        $order->country = $r->country;
        $order->postal_code = $r->postalCode;
        $order->phone = $r->phone;
        $order->email = $r->email;

        $order->charged_subtotal = $spi->metadata->chargedSubtotal;
        $order->charged_shipping_fee = $spi->metadata->chargedShippingFee;
        $order->charged_tax = $spi->metadata->chargedTax;

        $order->projected_total_delivery_days = $spi->metadata->projectedTotalDeliveryDays;
        $order->earliest_delivery_date = $spi->metadata->earliestDeliveryDate;
        $order->latest_delivery_date = $spi->metadata->latestDeliveryDate;

        $status = OrderStatusCacheObject::getDataByName('ORDER_CREATED');
        $order->status_code = $status->code;
        $order->save();


        $params['orderId'] = $manuallyGeneratedOrderId;
        $params['order'] = $order;
        $params['hasOrderBeenCreated'] = true;


        $params['resultCode'] = $status->code;
        $params['entireProcessLogs'][] = $status->readable_name;


        $this->createOrderItems($params);
    }



    private function createOrderItems(&$params)
    {
        $cartItems = $params['cartCO']->data->cartItems;

        foreach ($cartItems as $i) {

            $sellerProductCO = SellerProductCacheObject::getUpdatedModelCacheObjWithId($i->sellerProductId);

            $orderItem = new OrderItem();
            $orderItem->order_id = $params['orderId'];
            $orderItem->product_id = $i->productId;
            $orderItem->price = $sellerProductCO->getSellerProductPurchasePrice();
            $orderItem->quantity = $i->quantity;
            $orderItem->product_seller_id = $i->sellerProductId;
            $orderItem->size_availability_id = $i->sizeAvailabilityId;
            $orderItem->save();
        }


        $status = OrderStatusCacheObject::getDataByName('ORDER_ITEMS_CREATED');
        $params['resultCode'] = $status->code;
        $params['entireProcessLogs'][] = $status->readable_name;
    }



    private function updateInventoryQuantities(&$params)
    {

        $cartItems = $params['cartCO']->data->cartItems;

        foreach ($cartItems as $ci) {

            $sizeAvailabilityObj = SizeAvailability::find($ci->sizeAvailabilityId);

            $updatedQuantity = $sizeAvailabilityObj->quantity - $ci->quantity;
            $sizeAvailabilityObj->quantity = $updatedQuantity;
            $sizeAvailabilityObj->save();
        }


        $status = OrderStatusCacheObject::getDataByName('INVENTORY_QUANTITIES_UPDATED');
        $params['resultCode'] = $status->code;
        $params['entireProcessLogs'][] = $status->readable_name;
    }



    private function updateInventoryOrderLimits(&$params)
    {

        $cartItems = $params['cartCO']->data->cartItems;

        $cacheKey = 'inventoryOrderLimits';
        $inventoryOrderLimitsCO = new InventoryOrderLimitsCacheObject($cacheKey);

        $inventoryOrderLimitsCO->data['numOfDailyOrders'] += 1;
        $inventoryOrderLimitsCO->data['numOfDailyOrderItems'] += count($cartItems);
        $inventoryOrderLimitsCO->save();

        $status = OrderStatusCacheObject::getDataByName('INVENTORY_ORDER_LIMITS_UPDATED');
        $params['resultCode'] = $status->code;
        $params['entireProcessLogs'][] = $status->readable_name;
    }



    private function resetCacheCart(&$params)
    {
        $cartCO = $params['cartCO'];
        $cartCO->resetData();
        $params['newCartCO'] = $cartCO;

        $params['cacheCartHasBeenReset'] = true;

        $status = OrderStatusCacheObject::getDataByName('CACHE_CART_RESET_OK');
        $params['resultCode'] = $status->code;
        $params['entireProcessLogs'][] = $status->readable_name;
    }



    private function updateOrderStatus(&$params)
    {
        $order = $params['order'];
        if (isset($order)) {
            $status = OrderStatusCacheObject::getDataByName('ORDER_CONFIRMED');
            $order->status_code = $status->code;
            $order->save();

            $params['resultCode'] = $status->code;
            $params['entireProcessLogs'][] = $status->readable_name;
        }
    }



    private function createIncompleteOrderRecord(&$params)
    {
        if (!isset($params['stripePaymentIntent'])) {
            $status = OrderStatusCacheObject::getDataByName('MISSING_STRIPE_PAYMENT_INTENT_LINK');
            $params['resultCode'] = $status->code;
            $params['entireProcessLogs'][] = $status->readable_name;
            return;
        }

        $io = new IncompleteOrder();
        $io->cart_id = $params['cartId'];
        $io->user_id = $params['userId'];
        $io->order_id = $params['orderId'];
        $io->stripe_payment_intent_id = $params['stripePaymentIntentId'];
        $io->result_code = $params['resultCode'];
        $io->entire_process_logs = implode(',', $params['entireProcessLogs']);
        $io->save();
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

        $entireProcessParams = [
            'hasUserBeenCharged' => true,
            'cacheCartHasBeenReset' => false,
            'hasOrderBeenCreated' => false,
            'userId' => $userId,
            'user' => $user,
            'cartCO' => null,
            'cartObj' => null,
            'cartId' => 0,
            'entireProcessLogs' => [
                OrderStatusCacheObject::getReadableNameByName('PAYMENT_METHOD_CHARGED'),
                OrderStatusCacheObject::getReadableNameByName('START_OF_FINALIZING_ORDER')
            ],
            'resultCode' => OrderStatusCacheObject::getCodeByName('START_OF_FINALIZING_ORDER'),
            'stripePaymentIntentId' => null,
            'orderId' => 0,
            'order' => null,
            'request' => $request
        ];


        try {
            $this->checkCartCache($entireProcessParams);
            $this->updateInventoryQuantities($entireProcessParams);
            $this->updateInventoryOrderLimits($entireProcessParams);
            $this->createOrder($entireProcessParams);

            EmailUserOrderDetails::dispatch($entireProcessParams['order']->id)->onQueue(BmdGlobalConstants::QUEUE_FOR_EMAILING_ORDER_DETAILS);
        } catch (Exception $e) {
            $entireProcessParams['exception'] = $e;
            $this->handleEntireProcessException($entireProcessParams);
        }


        $this->deactivateDbCart($entireProcessParams);
        $this->resetCacheCart($entireProcessParams);

        if ($entireProcessParams['hasOrderBeenCreated']) {
            $this->updateOrderStatus($entireProcessParams);
        } else {
            $this->createIncompleteOrderRecord($entireProcessParams);
        }


        return [
            'objs' => [
                'orderProcessStatusCode' => $entireProcessParams['resultCode'],
                'orderId' => $entireProcessParams['orderId'],

                // BMD-FOR-DEBUG
                // BMD-ON-STAGING: Comment-out
                'entireProcessLogs' => $entireProcessParams['entireProcessLogs'],

                'updatedCart' => $entireProcessParams['newCartCO']->data ?? null
            ]
        ];
    }



    private function handleEntireProcessException(&$entireProcessParams)
    {

        $e = $entireProcessParams['exception'];

        $status = OrderStatusCacheObject::getDataByName('ORDER_FINALIZATION_EXCEPTION');
        $entireProcessParams['resultCode'] = $status->code;
        $entireProcessParams['entireProcessLogs'][] = $status->readable_name;

        $entireProcessParams['entireProcessLogs'][] = 'caught BMD-EXCEPTION ==> ...';
        $entireProcessParams['entireProcessLogs'][] = $e->getMessage();

        $eTrace = $e->getTrace();
        $entireProcessParams['entireProcessLogs'][] = 'caught BMD-EXCEPTION-TRACE ==> ...';

        // Log the first 3 errors.
        for ($i = 0; $i < 3; $i++) {
            if (!isset($eTrace[$i])) {
                break;
            }
            $eTraceMsg = 'CLASS ==> ' . $eTrace[$i]['class'] . ' | ';
            $eTraceMsg .= 'FILE ==> ' . $eTrace[$i]['file'] . ' | ';
            $eTraceMsg .= 'FUNC ==> ' . $eTrace[$i]['function'] . ' | ';
            $eTraceMsg .= 'LINE ==> ' . $eTrace[$i]['line'];
            $entireProcessParams['entireProcessLogs'][] = $eTraceMsg;
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
