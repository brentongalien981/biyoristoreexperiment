<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
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
