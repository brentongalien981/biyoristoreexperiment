<?php

namespace App\MyHelpers\Cart;

use App\Cart;
use App\Http\BmdCacheObjects\CartCacheObject;
use App\Http\BmdCacheObjects\ProductResourceCacheObject;
use App\Http\BmdCacheObjects\SellerProductCacheObject;
use App\Product;
use App\SellerProduct;

class CartVerifier
{

    public static function isItemWithSizeAlreadyInCart($data, $cart)
    {

        if (!isset($cart->cartItems)) { return false; }

        foreach ($cart->cartItems as $ci) {
            if (
                $ci->product->id == $data['productId']
                && $ci->sizeAvailabilityId == $data['sizeAvailabilityId']
            ) {
                return true;
            }
        }

        return false;
    }



    public static function verifyAddingItemToCartWithData($data)
    {
        // $cart = Cart::getUserCartFromCache($data['userId']);
        $cart = new CartCacheObject('cart?userId=' . $data['userId']);

        // Check if product with same size is already in the cart.
        if (self::isItemWithSizeAlreadyInCart($data, $cart)) {
            return Cart::RESULT_CODE_ADD_ITEM_ALREADY_EXISTS;
        }


        // $productToAdd = Product::getProductFromCache($data['productId'])['mainData'];
        $productToAddCO = ProductResourceCacheObject::getUpdatedResourceCacheObjWithId($data['productId']);

        // Verify that the seller-product-id is associated with the product.
        if (isset($productToAddCO)) {
            foreach ($productToAddCO->data->sellers as $s) {

                $sellerProductPivot = $s->pivot;

                if ($sellerProductPivot->id == $data['sellerProductId']) {

                    $sellerProductCO = SellerProductCacheObject::getUpdatedModelCacheObjWithId($data['sellerProductId']);
                    $sellerProductSizeAvailabilities = $sellerProductCO->data->sizeAvailabilities;

                    // Verify that the size-availability is associated with the seller-product.
                    foreach ($sellerProductSizeAvailabilities as $sizeAvailability) {
                        if ($sizeAvailability->id == $data['sizeAvailabilityId']) {
                            return Cart::RESULT_CODE_ADD_ITEM_OK_TO_ADD;
                        }
                    }
                }
            }
        }


        return Cart::RESULT_CODE_ADD_ITEM_DATA_MISMATCHES;
    }
}
