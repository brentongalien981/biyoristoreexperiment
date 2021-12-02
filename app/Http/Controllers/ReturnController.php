<?php

namespace App\Http\Controllers;

use Exception;
use App\BmdReturn;
use App\ReturnStatus;
use Illuminate\Http\Request;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Rules\ValidOrderReturnDateWindow;
use App\Rules\AllowedOrderStatusForOrderReturn;
use App\Http\BmdResponseCodes\OrderBmdResponseCodes;
use App\Http\BmdHttpResponseCodes\GeneralHttpResponseCodes;
use App\Http\BmdHttpResponseCodes\BmdReturnHttpResponseCodes;

class ReturnController extends Controller
{
    public function requestForReturn(Request $r)
    {
        $isResultOk = false;
        $resultCode = null;
        $bmdReturn = null;


        $r->validate([
            'orderId' => 'exists:orders,id'
        ]);
        

        try {

            // For logged-in users.
            BmdAuthProvider::setInstance($r->bmdToken, $r->authProviderId);
            $user = BmdAuthProvider::check() ? BmdAuthProvider::user() : null;

            if (BmdReturn::doesAlreadyExistWithOrderId($r->orderId)) {
                $resultCode = BmdReturnHttpResponseCodes::BMD_RETURN_ALREADY_EXISTS;
                throw new Exception($resultCode['readableMessage']);
            }

            $bmdReturn = new BmdReturn();
            $bmdReturn->order_id = $r->orderId;
            $bmdReturn->user_id = $user ? $user->id : null;
            $bmdReturn->status_code = ReturnStatus::getCodeByName('DEFAULT');
            $bmdReturn->save();


            $isResultOk = true;
        } catch (\Throwable $th) {
        }


        return [
            'isResultOk' => $isResultOk,
            'objs' => [
                'bmdReturn' => $bmdReturn
            ],
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
