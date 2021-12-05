<?php

namespace App\Http\Controllers;

use App\Order;
use Exception;
use App\BmdReturn;
use App\ReturnStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\MyConstants\BmdGlobalConstants;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Rules\ValidOrderReturnDateWindow;
use App\Rules\AllowedOrderStatusForOrderReturn;
use App\Http\BmdResponseCodes\OrderBmdResponseCodes;
use App\Http\BmdHttpResponseCodes\GeneralHttpResponseCodes;
use App\Http\BmdHttpResponseCodes\BmdReturnHttpResponseCodes;
use App\Mail\OrderReturnRequested;
use App\Rules\MinimumCombinedTotalQuantityOfOrderItemsAvailableLeftForOrderReturn;

class ReturnController extends Controller
{
    public function requestForReturn(Request $r)
    {
        $isResultOk = false;
        $resultCode = null;


        $r->validate([
            'orderId' => 'exists:orders,id'
        ]);
        

        $extraValidationData = [
            'orderId' => $r->orderId
        ];


        try {
            if (!AllowedOrderStatusForOrderReturn::bmdValidate($extraValidationData)) {
                $resultCode = OrderBmdResponseCodes::NOT_ALLOWED_ORDER_STATUS_FOR_ORDER_RETURN;
                throw new Exception();
            }

            if (!ValidOrderReturnDateWindow::bmdValidate($extraValidationData)) {
                $resultCode = OrderBmdResponseCodes::INVALID_ORDER_RETURN_DATE_WINDOW;
                throw new Exception();
            }
            
            if (!MinimumCombinedTotalQuantityOfOrderItemsAvailableLeftForOrderReturn::bmdValidate($extraValidationData)) {
                $resultCode = OrderBmdResponseCodes::ALL_ORDER_ITEMS_HAVE_BEEN_RETURNED;
                throw new Exception();
            }


            $order = Order::find($r->orderId);
            Mail::to(BmdGlobalConstants::CUSTOMER_SERVICE_EMAIL)
                ->bcc(BmdGlobalConstants::EMAIL_FOR_ORDER_EMAILS_TRACKER)
                ->send(new OrderReturnRequested($order));
                

            $isResultOk = true;
        } catch (Exception $e) {
                        
        }


        return [
            'isResultOk' => $isResultOk,
            'objs' => [],
            'resultCode' => $resultCode
        ];
    }



    public function create(Request $r)
    {
        $isResultOk = false;
        $resultCode = null;

        $extraValidationData = [
            'orderId' => $r->orderId
        ];


        try {
            if (!AllowedOrderStatusForOrderReturn::bmdValidate($extraValidationData)) {
                $resultCode = OrderBmdResponseCodes::NOT_ALLOWED_ORDER_STATUS_FOR_ORDER_RETURN;
                throw new Exception();
            }

            if (!ValidOrderReturnDateWindow::bmdValidate($extraValidationData)) {
                $resultCode = OrderBmdResponseCodes::INVALID_ORDER_RETURN_DATE_WINDOW;
                throw new Exception();
            }

            $isResultOk = true;
        } catch (Exception $e) {
            $isResultOk = false;
        }


        return [
            'isResultOk' => $isResultOk,
            'objs' => [],
            'resultCode' => $resultCode
        ];
    }
}
