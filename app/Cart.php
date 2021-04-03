<?php

namespace App;

use App\Http\Resources\CartResource;
use App\MyConstants\BmdGlobalConstants;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    public static function getUserCartFromCache($user) {
        $processLogs = ['In CLASS: Cart, METHOD: getUserCartFromCache()'];

        $cacheKey = 'cart?userId=' . $user->id;
        $cart = Cache::store('redisreader')->get($cacheKey);
        $retrievedDataFrom = BmdGlobalConstants::RETRIEVED_DATA_FROM_CACHE;


        if (!$cart) {
            $processLogs[] = 'user-cart does not exist from cache';
            $retrievedDataFrom = BmdGlobalConstants::RETRIEVED_DATA_FROM_DB;

            $carts = $user->carts()->where('is_active', 1)->take(1)->get();

            if (isset($carts) && count($carts) === 1) {
                $cart = new CartResource($carts[0]);
            } else {
                $processLogs[] = 'user-cart does not exist from db either';

                // Create a new cart-obj, but don't save it to db cause cart-db-record needs Stripe-payment-intent-id.
                $cart = new Cart();
                $cart->id = 0;
                $cart->user_id = $user->id;
                $cart->is_active = 1;
                $cart = new CartResource($cart);

                $processLogs[] = 'just created a cart-obj';
            }

            Cache::store('redisprimary')->put($cacheKey, $cart, now()->addDays(7));

            $processLogs[] = 'just saved the cart-obj to cache';
        } else {
            $processLogs[] = 'has just read user-cart from cache';
        }

        
        


        return [
            'mainData' => $cart,
            'processLogs' => $processLogs,
            'retrievedDataFrom' => $retrievedDataFrom
        ];
    }



    public function addItemWithProductId($productId)
    {
        foreach ($this->cartItems as $cartItem) {
            if ($cartItem->product_id == $productId) { return; }
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
