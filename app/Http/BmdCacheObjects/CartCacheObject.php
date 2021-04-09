<?php

namespace App\Http\BmdCacheObjects;

use App\Http\Resources\ProductResource;
use App\Product;

class CartCacheObject extends BmdCacheObject
{
    protected $lifespanInMin = 1440;



    /**
     * Update the product for each cart-item with the updated product-prices, quantity, etc.
     *
     * @param String $newCacheKey
     * @return CartCacheObject
     */
    public function getRenewedObj($newCacheKey) {

        $oldCartItems = $this->data->cartItems ?? [];
        $updatedCartItems = [];

        foreach ($oldCartItems as $ci) {
            $updatedCartItem = $ci;

            //bmd-todo: Make METHOD: ProductResourceCacheObj->getOrCreate().
            // You can use the code: if (getdate(lastRefreshInSec)['hours] > CLOSING_SITE_HOUR)...
            $updatedProduct= Product::find($ci->productId ?? $ci->product_id);
            $updatedProductResource = new ProductResource($updatedProduct);

            $updatedCartItem->product = $updatedProductResource;

            $updatedCartItems[] = $updatedCartItem;

        }


        $updatedCart = $this->data;
        $updatedCart->cartItems = $updatedCartItems;

        $updatedObj = new self($newCacheKey);
        $updatedObj->data = $updatedCart;
        $updatedObj->lastRefreshedInSec = $this->lastRefreshedInSec + (24 * 60 * 60);
        $updatedObj->save();
        

        return $updatedObj;
    }
}