<?php

namespace App\Http\BmdCacheObjects;

use App\Cart;
use App\Product;
use App\CartItem;
use App\Http\Resources\ProductResource;
use App\MyHelpers\Cart\CartVerifier;

class CartCacheObject extends BmdModelCacheObject
{
    protected $lifespanInMin = 1440;
    protected static $modelPath = Cart::class;



    public function __construct($cacheKey, $readerConnection = null)
    {
        parent::__construct($cacheKey, $readerConnection);

        if (!isset($this->entireData) || !isset($this->data)) {
            $this->initData();
        }
    }



    private function initData() {
        $cart = new Cart();
        $cart->id = 0;
        $cart->isActive = 1;
        $cart->cartItems = [];
        $this->data = $cart;
        $this->save();
    }



    public static function mergeCarts($mainCartCO, $otherCartCO) {

        $mainCart = $mainCartCO->data;
        $mergedCartItems = $mainCartCO->data->cartItems;
        $otherCartItems = $otherCartCO->data->cartItems;

        foreach ($otherCartItems as $ci) {
            if (!CartVerifier::isItemAlreadyInCart($ci, $mainCart)) {
                $mergedCartItems[] = $ci;
            }
        }

        $mainCartCO->data->cartItems = $mergedCartItems;
        $mainCartCO->save();
        return $mainCartCO;
    }



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
        $newCartItem->productId = $productCO->data->id;
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