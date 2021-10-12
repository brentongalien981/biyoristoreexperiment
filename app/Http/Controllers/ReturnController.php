<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function requestForReturn(Request $r)
    {
        $isResultOk = false;


        return [
            'isResultOk' => $isResultOk,
            'objs' => [
            ],
            // BMD-DELETE
            'requestData' => [
                'orderId' => $r->orderId
            ]
        ];
    }
}
