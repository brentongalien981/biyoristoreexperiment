<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
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
