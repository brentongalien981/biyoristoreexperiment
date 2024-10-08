<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Http\BmdCacheObjects\CartCacheObject;
use App\Product;
use App\SellerProduct;
use Illuminate\Http\Request;
use App\Http\Resources\CartResource;
use App\MyHelpers\Cart\CartVerifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\MyHelpers\General\GeneralHelper;
use Exception;

class CartController extends Controller
{
    public function mergeGuestAndActualUserCarts(Request $r)
    {
        $v = $r->validate([
            'temporaryGuestUserId' => 'required|string|size:32'
        ]);


        $userId = BmdAuthProvider::user()->id;
        $loggedInUserCartCacheKey = 'cart?userId=' . $userId;
        $guestCartCacheKey = 'cart?userId=' . $v['temporaryGuestUserId'];

        $loggedInUserCartCO = new CartCacheObject($loggedInUserCartCacheKey);
        $guestCartCO = new CartCacheObject($guestCartCacheKey);

        $mergedCartCO = CartCacheObject::mergeCarts($loggedInUserCartCO, $guestCartCO);

        return [
            // 'msg' => 'In CLASS: CartController, METHOD: mergeGuestAndActualUserCarts()...',
            'isResultOk' => true,
            'objs' => [
                'cart' => $mergedCartCO->data
            ]
        ];
    }



    public function tryExtendingCartLifespan(Request $r)
    {

        if (!GeneralHelper::isWithinStoreSiteDataUpdateMaintenancePeriod()) {
            throw new Exception('Invalid Time for Operation...');
        }

        $v = $r->validate([
            'oldTemporaryGuestUserId' => 'required|string|size:32',
            'newTemporaryGuestUserId' => 'required|string|size:32'
        ]);


        $oldCacheKey = 'cart?userId=' . $v['oldTemporaryGuestUserId'];
        $newCacheKey = 'cart?userId=' . $v['newTemporaryGuestUserId'];

        if (BmdAuthProvider::check()) {
            $userId = BmdAuthProvider::user()->id;
            $oldCacheKey = 'cart?userId=' . $userId;
            $newCacheKey = 'cart?userId=' . $userId;
        }

        $oldCartCacheO = new CartCacheObject($oldCacheKey);
        $updatedCart = $oldCartCacheO->getRenewedObj($newCacheKey);


        return [
            // 'msg' => 'In CLASS: CartController, METHOD: tryExtendingCartLifespan()...',
            'isResultOk' => true,
            'objs' => [
                'cart' => $updatedCart->data
            ]
        ];
    }



    public function deleteCartItem(Request $r)
    {
        $v = $r->validate([
            'sellerProductId' => 'required|numeric',
            'sizeAvailabilityId' => 'required|numeric'
        ]);

        $userId = $r->temporaryGuestUserId;
        if (BmdAuthProvider::check()) {
            $userId = BmdAuthProvider::user()->id;
        }


        // $updatedCart = Cart::getUserCartFromCache($userId)['mainData'];
        $cacheKey = 'cart?userId=' . $userId;
        $updatedCartCO = new CartCacheObject($cacheKey);
        $cartItems = $updatedCartCO->data->cartItems;
        $updatedCartItems = [];

        foreach ($cartItems as $ci) {
            if ($ci->sellerProductId == $v['sellerProductId'] && $ci->sizeAvailabilityId == $v['sizeAvailabilityId']) {
                continue;
            }

            $updatedCartItems[] = $ci;
        }

        $updatedCartCO->data->cartItems = $updatedCartItems;
        $updatedCartCO->save();


        return [
            'isResultOk' => true,
            'objs' => [
                'cart' => $updatedCartCO->data
            ],
        ];
    }



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


        $cacheKey = 'cart?userId=' . $userId;
        $updatedCartCO = new CartCacheObject($cacheKey);
        $cartItems = $updatedCartCO->data->cartItems;

        foreach ($cartItems as $ci) {
            if ($ci->sellerProductId == $v['sellerProductId'] && $ci->sizeAvailabilityId == $v['sizeAvailabilityId']) {
                $ci->quantity = $v['quantity'];
                break;
            }
        }

        $updatedCartCO->save();


        return [
            'isResultOk' => true,
            'objs' => [
                'cart' => $updatedCartCO->data
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
        $updatedCartCO = null;

        $resultCode = CartVerifier::verifyAddingItemToCartWithData($v);

        // Add item to cart if ok to do so..
        if ($resultCode == Cart::RESULT_CODE_ADD_ITEM_OK_TO_ADD) {
            $updatedCartCO = new CartCacheObject('cart?userId=' . $v['userId']);
            $updatedCartCO->addItemWithData($v);
            $resultCode = Cart::RESULT_CODE_ADD_ITEM_SUCCESSFUL;
        }


        return [
            'isResultOk' => true,
            'resultCode' => $resultCode,
            'objs' => [
                'cart' => isset($updatedCartCO) ? $updatedCartCO->data : null
            ],
        ];
    }



    public function read(Request $r)
    {
        $userId = $r->temporaryGuestUserId;

        if (BmdAuthProvider::check()) {
            $userId = BmdAuthProvider::user()->id;
        } 

        $cacheKey = 'cart?userId=' . $userId;
        $cartCO = new CartCacheObject($cacheKey);

        $updatedCartCO = $cartCO;
        if ($cartCO->shouldRefresh()) {
            $updatedCartCO = $cartCO->getRenewedObj($cacheKey);
        }


        return [
            'isResultOk' => true,
            'objs' => [
                'cart' => $updatedCartCO->data
            ],
        ];
    }
}
