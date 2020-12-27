<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShippingServiceLevel extends Model
{
    public static function findDeliveryDaysForService($serviceName, $shippingServiceLevels) {
        foreach ($shippingServiceLevels as $l) {
            if ($l->name == $serviceName) {
                return $l->latest_delivery_days;
            }
        }

        return 0;
    }
}
