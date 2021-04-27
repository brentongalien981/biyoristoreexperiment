<?php

namespace App\Http\BmdCacheObjects;

use App\Order;
use App\OrderItem;

class InventoryOrderLimitsCacheObject extends BmdCacheObject implements CustomCacheObjectInterface
{
    protected $lifespanInMin = 1440;



    public function __construct($cacheKey, $readerConnection = null)
    {
        parent::__construct($cacheKey, $readerConnection);

        if (!isset($this->entireData) || !isset($this->data) || $this->shouldRefresh()) {
            $this->initData();
        }
    }



    public function initData()
    {
        $this->data = [
            'numOfDailyOrderItems' => $this->getNumOfDailyOrderItems(),
            'numOfDailyOrders' => $this->getNumOfDailyOrders()
        ];
        $this->save();
    }



    private function getNumOfDailyOrders()
    {

        $numOfSecInDay = 86400;
        $dateTimeObjToday = getdate();
        $dateTimeObjTomorrow = getdate($dateTimeObjToday[0] + $numOfSecInDay);

        $dateTodayInStr = $dateTimeObjToday['year'] . '-' . $dateTimeObjToday['mon'] . '-' . $dateTimeObjToday['mday'];
        $dateTomorrowInStr = $dateTimeObjTomorrow['year'] . '-' . $dateTimeObjTomorrow['mon'] . '-' . $dateTimeObjTomorrow['mday'];

        $ordersToday = Order::where('created_at', '>=', $dateTodayInStr)
            ->where('created_at', '<', $dateTomorrowInStr)
            ->get();

        return count($ordersToday);
    }



    private function getNumOfDailyOrderItems()
    {

        $numOfSecInDay = 86400;
        $dateTimeObjToday = getdate();
        $dateTimeObjTomorrow = getdate($dateTimeObjToday[0] + $numOfSecInDay);

        $dateTodayInStr = $dateTimeObjToday['year'] . '-' . $dateTimeObjToday['mon'] . '-' . $dateTimeObjToday['mday'];
        $dateTomorrowInStr = $dateTimeObjTomorrow['year'] . '-' . $dateTimeObjTomorrow['mon'] . '-' . $dateTimeObjTomorrow['mday'];

        $orderItemsToday = OrderItem::where('created_at', '>=', $dateTodayInStr)
            ->where('created_at', '<', $dateTomorrowInStr)
            ->get();

        return count($orderItemsToday);
    }
}
