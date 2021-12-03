<?php

namespace App\Rules;

use App\Order;



class MinimumCombinedTotalQuantityOfOrderItemsAvailableLeftForOrderReturn
{
    /**
     * @param $d: validation-data
     */
    public static function bmdValidate($d)
    {
        $o = Order::find($d['orderId']);

        $orderItemsTotalQty = 0;
        $returnItemsCombinedTotalQty = 0;


        foreach ($o->orderItems as $oi) {
            $orderItemsTotalQty += $oi->quantity;
        }

        foreach ($o->returns as $r) {
            foreach ($r->returnItems as $ri) {
                $returnItemsCombinedTotalQty += $ri->quantity;
            }
        }

        // dump($returnItemsCombinedTotalQty);
        // dump($orderItemsTotalQty);


        if ($returnItemsCombinedTotalQty < $orderItemsTotalQty) {
            return true;
        }

        return false;
    }
}
