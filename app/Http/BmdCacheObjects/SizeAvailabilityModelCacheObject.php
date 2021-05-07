<?php

namespace App\Http\BmdCacheObjects;

use App\SizeAvailability;

class SizeAvailabilityModelCacheObject extends BmdModelCacheObject
{
    
    protected $lifespanInMin = 1440;
    protected static $modelPath = SizeAvailability::class;

    private function test_shit() {
        $path = static::class;
        $m = $path::getUpdatedModelCacheObjWithId(1);
    }

}