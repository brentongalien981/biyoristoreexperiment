<?php

namespace App;

use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\MyHelpers\Cache\CacheObjectsLifespanManager;

/**
 * buy_price is display-price of product on seller's website (w/o shipping-fee, shipping-fee-tax, item-tax)
 * sell_price is buy-price + intended-profit-margin (profit_margin is in Filipino "patong")
 * restock_days is the number of days it takes for ASB Inc. to buy the product from seller and for the product to arrive
 *      at ASB Inc.
 */

class Product extends Model
{
    public static function getProductFromCache($productId, $retrieveJsonResource = false) {

        $cacheKey = ($retrieveJsonResource ? 'productResource' : 'product') . '?id=' . $productId;

        $p = Cache::store('redisreader')->get($cacheKey);
        $shouldReferenceObjFromDb = false;

        if ($p) {
            if (CacheObjectsLifespanManager::shouldRefresh('product', $p)) {
                $shouldReferenceObjFromDb = true;
            }
        } else { $shouldReferenceObjFromDb = true; }


        if ($shouldReferenceObjFromDb) {
            $p = self::find($productId);
        }

        if (isset($p)) {
            $p = new ProductResource($p);
            $p->lastRefreshedInSec = $p->lastRefreshedInSec ?? getdate()[0];
            Cache::store('redisprimary')->put($cacheKey, $p, now()->addDays(1));
        }


        return [
            'mainData' => $p
        ];
    }



    public function reviews()
    {
        return $this->hasMany('App\Review');
    }



    public function team()
    {
        return $this->belongsTo('App\Team');
    }



    public function sellers()
    {
        return $this->belongsToMany('App\Seller')->withPivot('id', 'sell_price', 'discount_sell_price', 'restock_days', 'quantity');
    }



    public function productItem()
    {
        return $this->belongsTo('App\ProductItem');
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
