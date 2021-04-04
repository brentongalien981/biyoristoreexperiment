<?php

namespace App;

use App\Http\Resources\CartResource;
use App\MyConstants\BmdGlobalConstants;
use App\MyHelpers\Cache\CacheObjectsLifespanManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    public static function getUserCartFromCache($user)
    {
        $cacheKey = 'cart?userId=' . $user->id;
        $cart = Cache::store('redisreader')->get($cacheKey);
        $retrievedDataFrom = BmdGlobalConstants::RETRIEVED_DATA_FROM_CACHE;
        $shouldCreateNewCartObj = false;

        if ($cart) {
            if (CacheObjectsLifespanManager::shouldRefresh('cart', $cart)) {
                $shouldCreateNewCartObj = true;
            }
        } else {

            $retrievedDataFrom = BmdGlobalConstants::RETRIEVED_DATA_FROM_DB;
            $carts = $user->carts()->where('is_active', 1)->take(1)->get();

            if (isset($carts) && count($carts) === 1) {
                $cart = new CartResource($carts[0]);
            } else {
                $shouldCreateNewCartObj = true;
            }
        }


        if ($shouldCreateNewCartObj) {
            // Create a new cart-obj, but don't save it to db cause cart-db-record needs Stripe-payment-intent-id.
            $cart = new Cart();
            $cart->id = 0;
            $cart->user_id = $user->id;
            $cart->is_active = 1;
            $cart = new CartResource($cart);
        }

        $cart->lastRefreshedInSec = $cart->lastRefreshedInSec ?? getdate()[0];
        Cache::store('redisprimary')->put($cacheKey, $cart, now()->addDays(1));


        return [
            'mainData' => $cart,
            'retrievedDataFrom' => $retrievedDataFrom
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

    public function cartItems()
    {
        return $this->hasMany('App\CartItem');
    }
}
