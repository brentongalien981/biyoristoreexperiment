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
use App\IncompleteOrder;
use App\SizeAvailability;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\AddressResource;
use App\Http\Resources\ProfileResource;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\BmdCacheObjects\CartCacheObject;
use App\Http\BmdCacheObjects\OrderStatusCacheObject;
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
                'paymentProcessStatusCode' => $paymentProcessStatusCode,
                'orderProcessStatusCode' => $orderProcessStatusCode,
                'customeMsgs' => $customeMsgs,
                'customeError' => $customError,
                'order' => $order,
                'cart' => $cart,
            ];
        }
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
        $params['cartId'] = $cartCO->data->id;
        $params['stripePaymentIntentId'] = $cartCO->data->paymentIntentId;

        if (!isset($cartCO->data->id) || !isset($cartCO->data->cartItems)) {
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
        $cart = Cart::find($params['cartId']);
        $cart->is_active = 0;
        $cart->save();

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
        $spi = $this->retrieveStripePaymentIntent($params['cartCO']->data->paymentIntentId);
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

        $status = OrderStatusCacheObject::getDataByName('ORDER_CREATED');
        $order->status_code = $status->code;
        $order->save();


        $params['orderId'] = $manuallyGeneratedOrderId;
        $params['order'] = $order;


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

        $status = OrderStatusCacheObject::getDataByName('CACHE_CART_RESET_OK');
        $params['resultCode'] = $status->code;
        $params['entireProcessLogs'][] = $status->readable_name;
    }



    private function updateOrder(&$params)
    {
        $order = $params['order'];
        if (isset($order)) {
            $status = OrderStatusCacheObject::getDataByName('ORDER_BEING_PROCESSED');
            $order->status_code = $status->code;
            $order->save();

            $params['resultCode'] = $status->code;
            $params['entireProcessLogs'][] = $status->readable_name;
        }
    }



    private function createIncompleteOrderRecord(&$params)
    {
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
            'userId' => $userId,
            'user' => $user,
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
            $this->deactivateDbCart($entireProcessParams);
            $this->resetCacheCart($entireProcessParams);
            $this->updateOrder($entireProcessParams);


            // BMD-TODO: On DEV-ITER-002 / FEAT: Checkout / UC: app emails user of order-details.
            // - EVENT: OrderFinalized
            // - EVENT-HANDLER (QUEUEABLE): EmailUserOfOrderDetails

        } catch (Exception $e) {
            $entireProcessParams['exception'] = $e;
            $this->handleEntireProcessException($entireProcessParams);
        }


        return [
            'objs' => [
                'orderProcessStatusCode' => $entireProcessParams['resultCode'],
                'orderId' => $entireProcessParams['orderId'],

                // BMD-FOR-DEBUG
                // BMD-ON-STAGING: Comment-out
                'entireProcessLogs' => $entireProcessParams['entireProcessLogs'], 

                'newCart' => $entireProcessParams['newCartCO']->data ?? null
            ]
        ];
    }



    private function handleEntireProcessException(&$entireProcessParams)
    {

        $e = $entireProcessParams['exception'];

        $status = OrderStatusCacheObject::getDataByName('ORDER_FINALIZATION_FAILED');
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


        $this->createIncompleteOrderRecord($entireProcessParams);


        $entireProcessParams['entireProcessLogs'][] = 'caught BMD-EXCEPTION-TRACE-AS-STRING ==> ...';
        $entireProcessParams['entireProcessLogs'][] = $e->getTraceAsString();
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
