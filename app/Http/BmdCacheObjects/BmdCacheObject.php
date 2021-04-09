<?php

namespace App\Http\BmdCacheObjects;

use App\MyConstants\BmdGlobalConstants;
use App\MyHelpers\General\GeneralHelper;
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
    protected static $modelPath = null;


    public function __construct($cacheKey, $readerConnection = null)
    {
        $this->cacheKey = $cacheKey;

        $readerConnection = $readerConnection ?? $this->readConnection;
        $this->entireData = Cache::store($readerConnection)->get($cacheKey);
        $this->data = $this->entireData['data'] ?? null;
        $this->lastRefreshedInSec = $this->entireData['lastRefreshedInSec'] ?? null;
    }



    public static function getModelCacheKeyPrefix() {
        return static::$modelPath . '?id=';
    }



    public function save($params = []) {

        $entireData = [
            'data' => $this->data,
            'lastRefreshedInSec' => getdate()[0]
        ];

        $connection = $params['connection'] ?? $this->writeConnection;
        $cacheExpiryDate = $params['cacheExpiryDate'] ?? now()->addMinutes($this->lifespanInMin);

        Cache::store($connection)->put($this->cacheKey, $entireData, $cacheExpiryDate);
    }



    public function shouldRefresh()
    {
        if (!isset($this->entireData) || !isset($this->lastRefreshedInSec)) { return true; }
        
        $lastRefreshDateObj = getdate($this->lastRefreshedInSec);
        $nowInDateObj = getdate();

        if ($lastRefreshDateObj['year'] < $nowInDateObj['year']) {
            return true;
        }
        if ($lastRefreshDateObj['mon'] < $nowInDateObj['mon']) {
            return true;
        }
        if ($lastRefreshDateObj['mday'] < $nowInDateObj['mday']) {
            return true;
        }

        if (GeneralHelper::isWithinStoreSiteDataUpdateMaintenancePeriod($nowInDateObj)) { return true; }

        $elapsedTimeInMinSinceRefresh = intval(($nowInDateObj[0] - $lastRefreshDateObj[0]) / 60);
        if ($elapsedTimeInMinSinceRefresh > $this->lifespanInMin) {
            return true;
        }

        return false;
    }
}