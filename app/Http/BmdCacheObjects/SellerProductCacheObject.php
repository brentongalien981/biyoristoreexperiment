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


    // Return the purchase-price. The price that's always the lowest between regular-price and discounted-price.
    public function getSellerProductPurchasePrice() {
        $sellerProduct = $this->data;
        $regularSellPrice = floatval($sellerProduct->sell_price);
        $discountSellPrice = floatval($sellerProduct->discount_sell_price);

        $purchasePrice = $regularSellPrice;
        if ($discountSellPrice > 0 && $discountSellPrice < $regularSellPrice) {
            $purchasePrice = $discountSellPrice;
        }

        return $purchasePrice;
    }

}