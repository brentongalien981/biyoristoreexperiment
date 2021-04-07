<?php

namespace App;

use App\Http\Resources\CartResource;
use App\MyConstants\BmdGlobalConstants;
use App\MyHelpers\Cache\CacheObjectsLifespanManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    /** CONSTS */
    public const RESULT_CODE_ADD_ITEM_ALREADY_EXISTS = -1;
    public const RESULT_CODE_ADD_ITEM_DATA_MISMATCHES = -2;

    public const RESULT_CODE_ADD_ITEM_OK_TO_ADD = 1;
    public const RESULT_CODE_ADD_ITEM_SUCCESSFUL = 2;
    
    public const MAX_CART_ITEM_QUANTITY = 100;

    

    /** HELPER-FUNCS */
    public static function addItemToCartCacheWithData($data) {

        $cacheKey = 'cart?userId=' . $data['userId'];
        $cart = Cache::store('redisreader')->get($cacheKey);

        $product = Product::getProductFromCache($data['productId'], true)['mainData'];
        $newCartItem = new CartItem();
        $newCartItem->product_id = $data['productId'];
        $newCartItem->quantity = 1;
        $newCartItem->product = $product;
        $newCartItem->sellerProductId = $data['sellerProductId'];
        $newCartItem->sizeAvailabilityId = $data['sizeAvailabilityId'];

        $cartItems = $cart->cartItems ?? [];
        $cartItems[] = $newCartItem;
        $cart->cartItems = $cartItems;      
        
        Cache::store('redisprimary')->put($cacheKey, $cart);

        return $cart;
    }



    public static function getUserCartFromCache($userId)
    {
        $cacheKey = 'cart?userId=' . $userId;
        $cart = Cache::store('redisreader')->get($cacheKey);
        $shouldCreateNewCartObj = false;

        if ($cart) {
            if (CacheObjectsLifespanManager::shouldRefresh('cart', $cart)) {
                $shouldCreateNewCartObj = true;
            }
        } else { $shouldCreateNewCartObj = true; }


        if ($shouldCreateNewCartObj) {
            // Create a new cart-obj, but don't save it to db cause cart-db-record needs Stripe-payment-intent-id.
            $cart = new Cart();
            $cart->id = 0;
            $cart->is_active = 1;
            // $cart = new CartResource($cart);
        }

        $cart->lastRefreshedInSec = $cart->lastRefreshedInSec ?? getdate()[0];
        Cache::store('redisprimary')->put($cacheKey, $cart, now()->addDays(1));


        return [
            'mainData' => $cart
        ];
    }



    public function addItemWithProductId($productId)
    {
        foreach ($this->cartItems as $cartItem) {
            if ($cartItem->product_id == $productId) {
                return;
            }
        }

        $cartItem = new CartItem();
        $cartItem->cart_id = $this->id;
        $cartItem->product_id = $productId;
        $cartItem->quantity = 1;
        $cartItem->save();
    }



    /** MAIN-FUNCS */
    public function cartItems()
    {
        return $this->hasMany('App\CartItem');
    }
}
