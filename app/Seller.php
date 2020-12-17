<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    public function sellerAddress()
    {
        return $this->hasOne('App\SellerAddress');
    }



    public function products()
    {
        return $this->belongsToMany('App\Product');
    }
}
