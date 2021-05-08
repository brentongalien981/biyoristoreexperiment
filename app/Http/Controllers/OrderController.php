<?php

namespace App\Http\Controllers;

use App\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\BmdCacheObjects\OrderResourceCacheObject;
use App\Http\BmdCacheObjects\OrderStripePaymentIntentCacheObject;

class OrderController extends Controller
{
    public function show($id)
    {
        $orderCO = OrderResourceCacheObject::getUpdatedResourceCacheObjWithId($id);
        $order = $orderCO->data;
        $paymentInfo = null;
        $processLogs = [];
        $isResultOk = false;


        try {
            if (isset($order) && isset($order->stripe_payment_intent_id)) {

                $cacheKey = 'orderStripePaymentIntent?id=' . $order->stripe_payment_intent_id;
                $ospiCO = new OrderStripePaymentIntentCacheObject($cacheKey);


                if (!isset($ospiCO->data['paymentMethodObj'])) {

                    // BMD-ON-STAGING
                    $stripe = new \Stripe\StripeClient(env('STRIPE_SK'));

                    $paymentIntent = $stripe->paymentIntents->retrieve($order->stripe_payment_intent_id);

                    $paymentMethodId = $paymentIntent->payment_method;

                    $paymentMethod = $stripe->paymentMethods->retrieve($paymentMethodId);

                    $ospiCO->data = [
                        'paymentMethodObj' => $paymentMethod
                    ];
                    $ospiCO->save();
                }

                $paymentInfo = $ospiCO->data['paymentMethodObj'];
                $isResultOk = true;
            }
        } catch (Exception $e) {
            $processLogs[] = $e->getMessage();
        }



        return [
            'isResultOk' => $isResultOk,
            'objs' => [
                'order' => $order,
                'paymentInfo' => $paymentInfo
            ],
            'processLogs' => $processLogs
        ];
    }




    public function read(Request $r)
    {
        $user = BmdAuthProvider::user();
        $overallProcessLogs = ['In CLASS: OrderController, METHOD: read()'];

        // BMD-TODO: On DEV-ITER-004 / FEAT: Order
        // Re-implement with the use of BmdCacheObjects...
        $readData = Order::getUserOrdersDataFromCache($user, $r->pageNum);
        $orders = $readData['mainData'];
        $totalNumOfItems = $readData['totalNumOfItems'];
        $overallProcessLogs = array_merge($overallProcessLogs, $readData['processLogs']);


        return [
            'isResultOk' => true,
            'overallProcessLogs' => $overallProcessLogs, // BMD-ON-STAGING: Comment out.
            'objs' => [
                'orders' => $orders,
                'ordersMetaData' => [
                    'totalNumOfItems' => $totalNumOfItems,
                    'numOfItemsPerPage' => Order::NUM_OF_ITEMS_PER_PAGE,
                ]
            ],
        ];
    }



    public function index(Request $request)
    {
        $user = BmdAuthProvider::user();

        // Get the user orders based on the request's order-page-number.
        $skipNumOfItems = Order::NUM_OF_ITEMS_PER_PAGE * ($request->pageNum - 1);
        $userOrders = $user->orders()->orderBy('created_at', 'desc')->skip($skipNumOfItems)->take(Order::NUM_OF_ITEMS_PER_PAGE)->get();


        return [
            'isResultOk' => true,
            'message' => 'From CLASS: OrderController, METHOD: index()',
            'pageNum' => $request->pageNum,
            'objs' => [
                'orders' => OrderResource::collection($userOrders),
            ]
        ];
    }
}
