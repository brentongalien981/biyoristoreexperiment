<?php

namespace App\Http\BmdCacheObjects;

use App\Cart;
use App\Product;
use App\CartItem;
use App\Http\Resources\ProductResource;

class CartCacheObject extends BmdModelCacheObject
{
    protected $lifespanInMin = 1440;
    protected static $modelPath = Cart::class;



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

            $updatedProductRCO = ProductResourceCacheObject::getUpdatedResourceCacheObjWithId($ci->productId ?? $ci->product_id);

            $updatedCartItem->product = $updatedProductRCO->data;

            $updatedCartItems[] = $updatedCartItem;

        }


        $updatedCart = $this->data ?? new Cart();
        $updatedCart->cartItems = $updatedCartItems;

        $updatedObj = new self($newCacheKey);
        $updatedObj->data = $updatedCart;
        $updatedObj->save();
        

        return $updatedObj;
    }



    public function addItemWithData($data) {

        $productCO = ProductResourceCacheObject::getUpdatedResourceCacheObjWithId($data['productId']);
        
        $newCartItem = new CartItem();
        $newCartItem->product_id = $productCO->data->id;
        $newCartItem->productid = $productCO->data->id;
        $newCartItem->quantity = 1;
        $newCartItem->product = $productCO->data;

        $newCartItem->sellerProductId = $data['sellerProductId'];
        $newCartItem->sizeAvailabilityId = $data['sizeAvailabilityId'];

        $cartItems = $this->data->cartItems ?? [];
        $cartItems[] = $newCartItem;
        $this->data->cartItems = $cartItems;      
        
        $this->save();
    }



    public function test_shit() {
        $CCO = App\Http\BmdCacheObjects\CartCacheObject::class;
        $k = 'cart?userId=1';
        $oldCartCO = new $CCO($k);

    }
}