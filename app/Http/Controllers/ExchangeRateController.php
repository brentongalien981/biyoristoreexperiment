<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function getRate(Request $r) {
        return [
            'isResultOk' => true,
            'msg' => 'In CLASS: ExchangeRateController',
            'from' => $r->from,
            'to' => $r->to
        ];
    }
}
