<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    const INVALID_CART = 1;
    const VALID_CART = 2;
    const CART_HAS_ITEM = 3;
    const CART_HAS_NO_ITEM = 4;
    const INVALID_PAYMENT_METHOD = 5;
    const WAITING_FOR_PAYMENT = 6;
    const PAYMENT_METHOD_CHARGED = 7;
    const CART_CHECKEDOUT_OK = 8;
    const CANCELLED = 9;
    const ORDER_CREATED = 10;
    const ORDER_ITEMS_CREATED = 11;


}
