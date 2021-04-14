<?php

namespace App\Http\BmdCacheObjects;


class UserStripePaymentMethodsCacheObject extends BmdModelCacheObject
{
    protected $lifespanInMin = 1440;


    /**
     * @override
     * @return UserStripePaymentMethodsCacheObject
     */
    public function getMyRefreshedVersion()
    {
        return $this;
    }

}