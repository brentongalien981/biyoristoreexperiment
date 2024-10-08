<?php

namespace App\MyHelpers\Cache;

class CacheObjectsLifespanManager
{

    private static function getObjTypeLifespanInMin($objType)
    {
        switch ($objType) {
            case 'cart':
            case 'product':
            case 'sizeAvailabilities':
                return 1440;
            default:
                return 60;
        }
    }



    public static function shouldRefresh($objType, $cacheRecord)
    {
        $lastRefreshedInSec = $cacheRecord->lastRefreshedInSec ?? $cacheRecord['lastRefreshedInSec'];
        $lastRefreshDateObj = getdate($lastRefreshedInSec);
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

        $elapsedTimeInMinSinceRefresh = intval((getdate()[0] - $lastRefreshDateObj[0]) / 60);
        if ($elapsedTimeInMinSinceRefresh >= self::getObjTypeLifespanInMin($objType)) {
            return true;
        }

        return false;
    }
}
