<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    const INVALID_CART = 1;
    const VALID_CART = 2;
    const CART_HAS_ITEM = 3;
    const CART_HAS_NO_ITEM = 4;
    const WAITING_FOR_PAYMENT = 5;
    const PAYMENT_METHOD_CHARGED = 6;
    const CANCELLED = 7;
}
