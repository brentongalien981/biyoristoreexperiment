<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    // const INVALID_CART = self::getIdByName('INVALID_CART');
    // const VALID_CART = self::where('name', 'VALID_CART')->id;
    // const CART_HAS_ITEM = self::where('name', 'CART_HAS_ITEM')->id;
    // const CART_HAS_NO_ITEM = self::where('name', 'CART_HAS_NO_ITEM')->id;
    // const INVALID_PAYMENT_METHOD = self::where('name', 'INVALID_PAYMENT_METHOD')->id;
    // const WAITING_FOR_PAYMENT = self::where('name', 'WAITING_FOR_PAYMENT')->id;
    // const PAYMENT_METHOD_CHARGED = self::where('name', 'PAYMENT_METHOD_CHARGED')->id;
    // const CART_CHECKEDOUT_OK = self::where('name', 'CART_CHECKEDOUT_OK')->id;
    // const CANCELLED = self::where('name', 'CANCELLED')->id;
    // const ORDER_CREATED = self::where('name', 'ORDER_CREATED')->id;
    // const ORDER_ITEMS_CREATED = self::where('name', 'ORDER_ITEMS_CREATED')->id;



    public static function getIdByName($name) {
        return self::where('name', $name)->get()[0]->id;
    }


}
