<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BmdReturn extends Model
{
    protected $table = 'returns';


    public static function doesAlreadyExistWithOrderId($orderId)
    {
        $bmdReturn = self::where('order_id', $orderId)->get()[0] ?? null;

        if (isset($bmdReturn)) { return true; }
        return false;
    }
}
