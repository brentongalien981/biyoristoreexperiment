<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    const WAITING_FOR_PAYMENT = 1;
    const PAID = 2;
    const CANCELLED = 3;
}
