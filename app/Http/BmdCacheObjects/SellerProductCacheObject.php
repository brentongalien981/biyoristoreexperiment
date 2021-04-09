<?php

namespace App\Http\BmdCacheObjects;

use App\SellerProduct;

class SellerProductCacheObject extends BmdModelCacheObject
{
    
    protected $lifespanInMin = 1440;
    protected static $modelPath = SellerProduct::class;

    private function test_shit() {
        $path = App\Http\BmdCacheObjects\SellerProductCacheObject::class;
        $m = $path::getUpdatedModelCacheObjWithId(1);
    }

}