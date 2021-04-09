<?php

namespace App\Http\BmdCacheObjects;

use App\Http\Resources\ProductResource;
use App\Product;

class ProductResourceCacheObject extends BmdResourceCacheObject
{
    protected $lifespanInMin = 1440;
    protected static $modelPath = Product::class;
    protected static $jsonResourcePath = ProductResource::class;



    public function test_shit() {
        $ClassProductRCO = App\Http\BmdCacheObjects\ProductResourceCacheObject::class;
        $modelId = 1;
        $prco = $ClassProductRCO::getOrPut($modelId);

    }

}