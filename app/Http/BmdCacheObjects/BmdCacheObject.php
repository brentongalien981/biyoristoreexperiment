<?php

namespace App\Http\BmdCacheObjects;

use Illuminate\Support\Facades\Cache;

class BmdCacheObject
{
    public $lastRefreshedInSec;
    public $cacheKey;
    protected $entireData;
    public $data;
    protected $writeConnection = 'redisprimary';
    protected $readConnection = 'redisreader';
    protected $lifespanInMin = 60;


    public function __construct($cacheKey, $readerConnection = 'redisreader')
    {
        $this->cacheKey = $cacheKey;
        $this->entireData = Cache::store($readerConnection)->get($cacheKey);
        $this->data = $this->entireData['data'] ?? null;
        $this->lastRefreshedInSec = $this->entireData['lastRefreshedInSec'] ?? null;
    }



    public function save($params = []) {

        $entireCacheData = [
            'data' => $this->data,
            'lastRefreshedInSec' => $this->lastRefreshedInSec ?? getdate()[0];
        ];

        $connection = $params['connection'] ?? $this->writeConnection;
        $cacheExpiryDate = $params['cacheExpiryDate'] ?? now()->addMinutes($this->lifespanInMin);

        Cache::store($connection)->put($this->cacheKey, $entireCacheData, $cacheExpiryDate);
    }



    public function getData($connection = $this->readConnection) {
        $entireData = Cache::store($connection)->get($this->cacheKey);
        return $entireData['data'] ?? null;
    }
}