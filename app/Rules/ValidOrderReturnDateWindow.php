<?php

namespace App\Rules;

use App\Order;
use App\MyConstants\BmdGlobalConstants;

class ValidOrderReturnDateWindow
{

    /**
     * @param $d: validation-data
     */
    public static function bmdValidate($d)
    {

        $o = Order::find($d['orderId']);

        $latestDeliveryTimestamp = strtotime($o->latest_delivery_date);
        $numOfSecFromDeliveryToNow = getdate()[0] - $latestDeliveryTimestamp;

        $numOfDaysFromDeliveryToNow = intval($numOfSecFromDeliveryToNow / BmdGlobalConstants::NUM_OF_SEC_IN_DAY);

        // dd($numOfDaysFromDeliveryToNow);

        if (
            $numOfDaysFromDeliveryToNow > 0 &&
            $numOfDaysFromDeliveryToNow <= BmdGlobalConstants::NUM_OF_ORDER_RETURN_WINDOW_DAYS
        ) {
            return true;
        }

        return false;
    }
}
