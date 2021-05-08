<?php

namespace App\Http\BmdCacheObjects;

class OrderStripePaymentIntentCacheObject extends BmdCacheObject implements CustomCacheObjectInterface
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
            'paymentMethodObj' => null
        ];
        $this->save();
    }
}
