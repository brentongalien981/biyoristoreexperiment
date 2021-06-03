<?php

namespace App\Http\BmdCacheObjects;

use App\ExchangeRate;

class ExchangeRateCacheObject extends BmdCacheObject
{
    protected $lifespanInMin = 1440;



    public static function getConversionRate($from, $to) {

        $cacheKey = 'exchangeRate?from=' . $from . '&to=' . $to;
        $exchangeRateCO = new self($cacheKey);

        if (!isset($exchangeRateCO->entireData) || !isset($exchangeRateCO->data) || $exchangeRateCO->shouldRefresh()) {
            $xRates = ExchangeRate::where('from', $from)->where('to', $to)->get();
            $exchangeRateCO->data = $xRates ? $xRates[0] : null;
            $exchangeRateCO->save();
        }

        return $exchangeRateCO->data;
    }

}