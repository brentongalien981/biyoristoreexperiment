<?php

namespace App\Http\BmdCacheObjects;

use App\OrderStatus;

class OrderStatusCacheObject extends BmdCacheObject
{
    protected $lifespanInMin = 1440;



    public static function getIdByName($name) {

        $cacheKey = 'orderStatus?name=' . $name;
        $orderStatusCO = new self($cacheKey);

        if (!isset($orderStatusCO->entireData) || !isset($orderStatusCO->data) || $orderStatusCO->shouldRefresh()) {
            $orderStatusCO->data = OrderStatus::where('name', $name)->get()[0] ?? null;
            $orderStatusCO->save();
        }

        return $orderStatusCO->data->id;
    }

}