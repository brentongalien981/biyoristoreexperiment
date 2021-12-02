<?php

namespace App\Rules;

use App\Order;
use App\OrderStatus;


class AllowedOrderStatusForOrderReturn
{
    /**
     * @param $d: validation-data
     */
    public static function bmdValidate($d)
    {
        $o = Order::find($d['orderId']);
        $oStatusName = OrderStatus::where('code', $o->status_code)->get()[0]->name;


        switch ($oStatusName) {
            case 'DISPATCHED':
            case 'DELIVERED':
                return true;
                break;
        }

        return false;
    }
}
