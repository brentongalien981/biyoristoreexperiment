<?php

namespace App;

use stdClass;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\MyHelpers\Cache\CacheObjectsLifespanManager;

class SellerProduct extends Model
{
    protected $table = 'product_seller';



    public static function getSizeAvailabilitiesFromCache($sellerProductPivotId) {

        $cacheKey = 'sizeAvailabilities?sellerProductPivotId=' . $sellerProductPivotId;
        $mainData = Cache::store('redisreader')->get($cacheKey);
        $shouldReferenceDb = false;

        if ($mainData) {
            if (CacheObjectsLifespanManager::shouldRefresh('sizeAvailabilities', $mainData)) {
                $shouldReferenceDb = true;
            }
        } else { $shouldReferenceDb = true; }


        $sellerProduct = null;
        if ($shouldReferenceDb) {
            $sellerProduct = self::find($sellerProductPivotId);
            $mainData = new stdClass();
        }

        if (isset($sellerProduct)) {
            $mainData->objs = $sellerProduct->sizeAvailabilities;
            $mainData->lastRefreshedInSec = $mainData->lastRefreshedInSec ?? getdate()[0];
            Cache::store('redisprimary')->put($cacheKey, $mainData, now()->addDays(1));
        }


        return [
            'mainData' => $mainData
        ];
    }


    public function sizeAvailabilities()
    {
        return $this->hasMany('App\SizeAvailability', 'seller_product_id', 'id');
    }
}
