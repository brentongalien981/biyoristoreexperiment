<?php

namespace App\Http\BmdCacheObjects;

use App\Http\Resources\ProfileResource;
use App\Profile;

class ProfileResourceCacheObject extends BmdResourceCacheObject
{
    protected $lifespanInMin = 1440;
    protected static $modelPath = Profile::class;
    protected static $jsonResourcePath = ProfileResource::class;
}