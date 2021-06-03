<?php

namespace App\Http\Controllers;

use App\Http\BmdCacheObjects\ExchangeRateCacheObject;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function getRate(Request $r) {

        $conversionRate = ExchangeRateCacheObject::getConversionRate($r->from, $r->to);


        return [
            'isResultOk' => $conversionRate ? true : false,
            'msg' => 'In CLASS: ExchangeRateController',
            'objs' => [
                'conversionRate' => $conversionRate
            ]
        ];
    }
}
