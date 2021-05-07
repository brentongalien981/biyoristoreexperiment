<?php

namespace App;

use App\Http\Resources\OrderResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Order extends Model
{
    // BMD-ON-STAGING
    const NUM_OF_ITEMS_PER_PAGE = 2;
    const ALL_USER_ORDERS_LIFESPAN_IN_MIN = 1;



    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;



    public static function getDeliveryDate($numOfBusinessDeliveryDays)
    {
        $incrementalDays = 0;
        $limit = 30;

        for ($i = 0; $i < $limit; $i++) {

            $aDateTime = now()->addDays($i+1);

            if (self::isWeekDay($aDateTime)) {
                $incrementalDays++;
                if ($incrementalDays == $numOfBusinessDeliveryDays) {
                    return $aDateTime;
                }
            } else {
                continue;
            }
        }
    }



    public static function getReadableDate($dateTime) {
        $d = getdate(strtotime($dateTime));

        $str = $d['weekday'] . ', ' . $d['month'] . ' ' . $d['mday'] . ', ' . $d['year'];
        return $str;
    }



    private static function isWeekDay($aDateTime)
    {
        $aDate = getdate(strtotime($aDateTime));

        switch ($aDate['wday']) {
            case 0:
            case 6:
                return false;

            default:
                return true;
        }
    }



    public static function getUserOrdersDataFromCache($user, $pageNum)
    {

        $processLogs = ['In CLASS: Order, METHOD: getUserOrdersDataFromCache()'];
        $cacheKey = 'orders?userId=' . $user->id;

        $allUserOrders = Cache::store('redisreader')->get($cacheKey);

        if ($allUserOrders) {
            $processLogs[] = 'allUserOrders has been read from cache';
        } else {
            $processLogs[] = 'allUserOrders were not found from cache';

            $allUserOrders = $user->orders()->orderBy('created_at', 'desc')->get();
            $processLogs[] = 'allUserOrders has been read from db';

            Cache::store('redisprimary')->put($cacheKey, $allUserOrders, now()->addMinutes(self::ALL_USER_ORDERS_LIFESPAN_IN_MIN));

            $processLogs[] = 'allUserOrders has been saved to cache';
        }


        $chunkOrders = [];
        $skipNumOfItems = self::NUM_OF_ITEMS_PER_PAGE * ($pageNum - 1);
        $startIndexOfPageOrders = $skipNumOfItems;
        $endIndexOfPageOrders = $skipNumOfItems + self::NUM_OF_ITEMS_PER_PAGE;

        for ($i = $startIndexOfPageOrders; $i < $endIndexOfPageOrders; $i++) {
            if (!isset($allUserOrders[$i])) {
                break;
            }
            $chunkOrders[] = $allUserOrders[$i];
        }


        $totalNumOfItems = count($allUserOrders);
        $chunkOrders = OrderResource::collection($chunkOrders);

        return [
            'mainData' => $chunkOrders,
            'totalNumOfItems' => $totalNumOfItems,
            'processLogs' => $processLogs
        ];
    }



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
