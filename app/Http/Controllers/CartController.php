<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Product;
use App\SellerProduct;
use Illuminate\Http\Request;
use App\Http\Resources\CartResource;
use App\MyHelpers\Cart\CartVerifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\BmdHelpers\BmdAuthProvider;

class CartController extends Controller
{

    public function updateCartItemCount(Request $r)
    {
        $v = $r->validate([
            'sellerProductId' => 'required|numeric',
            'sizeAvailabilityId' => 'required|numeric',
            'quantity' => 'required|integer|min:1|max:' . Cart::MAX_CART_ITEM_QUANTITY
        ]);

        $userId = $r->temporaryGuestUserId;
        if (BmdAuthProvider::check()) {
            $userId = BmdAuthProvider::user()->id;
        }


        $updatedCart = Cart::getUserCartFromCache($userId)['mainData'];
        $cartItems = $updatedCart->cartItems ?? [];

        foreach ($cartItems as $ci) {
            if ($ci->sellerProductId == $v['sellerProductId'] && $ci->sizeAvailabilityId == $v['sizeAvailabilityId']) {
                $ci->quantity = $v['quantity'];
                break;
            }
        }

        $cacheKey = 'cart?userId=' . $userId;
        Cache::store('redisprimary')->put($cacheKey, $updatedCart);


        return [
            'isResultOk' => true,
            'objs' => [
                'cart' => $updatedCart
            ],
        ];
    }



    public function addItem(Request $r)
    {

        // Validate the request-params.
        $v = $r->validate([
            'productId' => 'required|numeric',
            'sizeAvailabilityId' => 'required|numeric',
            'sellerProductId' => 'required|numeric',
            'temporaryGuestUserId' => 'nullable|string|max:32',
        ]);


        $userId = $r->temporaryGuestUserId;
        if (BmdAuthProvider::check()) {
            $userId = BmdAuthProvider::user()->id;
        }

        $v['userId'] = $userId;
        $updatedCart = null;

        $resultCode = CartVerifier::verifyAddingItemToCartWithData($v);

        // bmd-todo: Add item to cart if ok to do so..
        if ($resultCode == Cart::RESULT_CODE_ADD_ITEM_OK_TO_ADD) {
            $updatedCart = Cart::addItemToCartCacheWithData($v);
            $resultCode = Cart::RESULT_CODE_ADD_ITEM_SUCCESSFUL;
        }


        return [
            'isResultOk' => true,
            'resultCode' => $resultCode,
            'objs' => [
                'cart' => $updatedCart
            ],
        ];
    }



    public function read(Request $r)
    {
        $userId = null;

        if (BmdAuthProvider::check()) {
            $userId = BmdAuthProvider::user()->id;
        } else {
            $userId = $r->temporaryGuestUserId;
        }

        $resultData = Cart::getUserCartFromCache($userId);
        $cart = $resultData['mainData'];


        return [
            'isResultOk' => true,
            'objs' => [
                'cart' => $cart
            ],
        ];
    }
}
