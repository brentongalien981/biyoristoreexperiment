<?php

namespace App\Http\BmdCacheObjects;

use App\Address;
use App\Http\Resources\AddressResource;

class AddressResourceCollectionCacheObject extends BmdResourceCollectionCacheObject
{
    protected $lifespanInMin = 1440;
    protected static $modelPath = Address::class;
    protected static $jsonResourcePath = AddressResource::class;
    protected static $foreignKeyName = 'user_id';
}