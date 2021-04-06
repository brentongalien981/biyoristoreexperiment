<?php

namespace App\MyHelpers\Cart;

use App\Cart;
use App\Product;
use App\SellerProduct;

class CartVerifier
{

    public static function isItemWithSizeAlreadyInCart($data, $cart)
    {

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
        $cart = Cart::getUserCartFromCache($data['userId']);

        // Check if product with same size is already in the cart.
        if (self::isItemWithSizeAlreadyInCart($data, $cart)) {
            return Cart::RESULT_CODE_ADD_ITEM_ALREADY_EXISTS;
        }


        $productToAdd = Product::getProductFromCache($data['productId'])['mainData'];

        // Verify that the seller-product-id is associated with the product.
        if (isset($productToAdd)) {
            foreach ($productToAdd->sellers as $s) {

                $sellerProductPivot = $s->pivot;

                if ($sellerProductPivot->id == $data['sellerProductId']) {

                    //bmd-todo
                    $sellerProductSizeAvailabilities = SellerProduct::getSizeAvailabilitiesFromCache($sellerProductPivot->id)['mainData'];

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
