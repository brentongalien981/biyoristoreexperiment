<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * buy_price is display-price of product on seller's website (w/o shipping-fee, shipping-fee-tax, item-tax)
 * sell_price is buy-price + intended-profit-margin (profit_margin is in Filipino "patong")
 * restock_days is the number of days it takes for ASB Inc. to buy the product from seller and for the product to arrive
 *      at ASB Inc.
 */

class Product extends Model
{
    public function team()
    {
        return $this->belongsTo('App\Team');
    }



    public function sellers()
    {
        return $this->belongsToMany('App\Seller')->withPivot('id', 'sell_price', 'discount_sell_price', 'restock_days');
    }



    public function cartItem()
    {
        return $this->belongsTo('App\CartItem');
    }

    public function productPhotoUrls()
    {
        return $this->hasMany('App\ProductPhotoUrl');
    }

    public function brand()
    {
        return $this->belongsTo('App\Brand');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Category', 'product_category');
    }
}
