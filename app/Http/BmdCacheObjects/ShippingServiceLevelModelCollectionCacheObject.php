<?php

namespace App\Http\BmdCacheObjects;

use App\ShippingServiceLevel;


class ShippingServiceLevelModelCollectionCacheObject extends BmdModelCollectionCacheObject
{  
    protected $lifespanInMin = 1440;
    protected static $modelPath = ShippingServiceLevel::class;

}