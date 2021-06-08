<?php

namespace App\Http\BmdCacheObjects;

use App\Http\Resources\OrderResource;
use App\Order;

class OrderResourceCacheObject extends BmdResourceCacheObject
{
    protected $lifespanInMin = 120;
    protected static $modelPath = Order::class;
    protected static $jsonResourcePath = OrderResource::class;
}