<?php

namespace App\Http\Controllers;

use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use Exception;

class OrderController extends Controller
{
    public function show($id) {

        $order = Order::find($id);

        return [
            'message' => 'From CLASS: OrderController, METHOD: show()',
            'orderId' => $id,
            'objs' => [
                'order' => isset($order) ? new OrderResource($order) : null
            ]
        ];
    }



    public function index(Request $request)
    {
        $user = Auth::user();



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
