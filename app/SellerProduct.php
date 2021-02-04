<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SellerProduct extends Model
{
    protected $table = 'product_seller';


    public function sizeAvailabilities()
    {
        return $this->hasMany('App\SizeAvailability', 'seller_product_id', 'id');
    }
}
