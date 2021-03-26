<?php

namespace App\Http\Controllers;

use App\Http\BmdHelpers\BmdAuthProvider;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use Exception;

class OrderController extends Controller
{
    public function show($id)
    {

        $order = Order::find($id);
        $paymentInfo = null;



        try {
            if (isset($order)) {

                $stripe = new \Stripe\StripeClient(env('STRIPE_SK'));

                $paymentIntent = $stripe->paymentIntents->retrieve($order->stripe_payment_intent_id);

                $paymentMethodId = $paymentIntent->payment_method;

                $paymentMethod = $stripe->paymentMethods->retrieve($paymentMethodId);
                $paymentInfo = $paymentMethod;
            }
        } catch (Exception $e) {
        }



        return [
            'message' => 'From CLASS: OrderController, METHOD: show()',
            'orderId' => $id,
            'objs' => [
                'order' => isset($order) ? new OrderResource($order) : null,
                'paymentInfo' => $paymentInfo
            ]
        ];
    }




    public function read(Request $r)
    {
        
        $user = BmdAuthProvider::user();

        // Get the user orders based on the request's order-page-number.
        $allUserOrders = $user->orders;
        $totalNumOfItems = count($allUserOrders);
        $skipNumOfItems = Order::NUM_OF_ITEMS_PER_PAGE * ($r->pageNum - 1);
        $chunkUserOrders = $user->orders()->orderBy('created_at', 'desc')->skip($skipNumOfItems)->take(Order::NUM_OF_ITEMS_PER_PAGE)->get();


        return [
            'isResultOk' => true,
            'objs' => [
                'orders' => OrderResource::collection($chunkUserOrders),
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

        // TODO: Get the user orders based on the request's order-page-number.
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
