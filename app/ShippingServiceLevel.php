<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * BMD-ON-ITER: Staging, Deployment: Update TABLE "shipping_service_levels" to have
 * values in accordance to UPS records.
 */
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
