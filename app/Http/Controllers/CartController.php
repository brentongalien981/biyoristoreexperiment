<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\Resources\CartResource;
use App\MyHelpers\Cart\CartVerifier;
use App\Product;
use App\SellerProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function updateUserCartCache(Request $r)
    {
        $u = BmdAuthProvider::user();
        // bmd-todo
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
