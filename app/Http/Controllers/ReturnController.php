<?php

namespace App\Http\Controllers;

use App\BmdReturn;
use Illuminate\Http\Request;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\BmdHttpResponseCodes\BmdReturnHttpResponseCodes;
use App\Http\BmdHttpResponseCodes\GeneralHttpResponseCodes;
use App\ReturnStatus;
use Exception;

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
}
