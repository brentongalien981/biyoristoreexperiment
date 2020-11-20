<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentStatus extends Model
{
    const WAITING_FOR_PAYMENT = 1;
    const PAYMENT_METHOD_CHARGED = 2;
}
