<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    const NUM_OF_ITEMS_PER_PAGE = 2;



    //
    public static function getOrderAmountInCents($items)
    {

        $orderTotalAmount = 0;
        $tax = 0.13;

        foreach ($items as $i) {
            $i = json_decode($i);
            $product = Product::find($i->productId);
            $quantity = $i->quantity;
            $itemTotalPrice = $product->price * $quantity;

            $orderTotalAmount += $itemTotalPrice;
        }

        $orderTotalAmount = $orderTotalAmount * (1 + $tax);

        $orderTotalAmountInCents = round($orderTotalAmount, 2) * 100;
        return $orderTotalAmountInCents;
    }



    public function status()
    {
        return $this->belongsTo('App\OrderStatus');
    }



    public function orderItems()
    {
        return $this->hasMany('App\OrderItem');
    }
}
