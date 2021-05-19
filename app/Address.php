<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use App\Http\Resources\AddressResource;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    public static function clearCacheAddressesWithUserId($userId) {
        $processLogs = ['In CLASS: Address, METHOD: clearCacheAddressesWithUserId()'];

        $cacheKey = 'addresses?userId=' . $userId;

        Cache::store('redisprimary')->forget($cacheKey);
        $processLogs[] = 'cleared user-addresses in cache';


        return [
            'processLogs' => $processLogs
        ];
    }



    public static function getAddressesFromCacheWithUser($user)
    {

        $processLogs = ['In CLASS: Address, METHOD: getAddressesFromCacheWithUser()'];

        $cacheKey = 'addresses?userId=' . $user->id;
        $mainData = Cache::store('redisreader')->get($cacheKey);

        if (isset($mainData)) {
            $processLogs[] = 'mainData is from cache';
        } else {

            $mainData = AddressResource::collection($user->addresses);
            $processLogs[] = 'has just read mainData from db';


            Cache::store('redisprimary')->put($cacheKey, $mainData, now()->addMinutes(2));
            $processLogs[] = 'has just saved mainData to cache';
        }


        return [
            'mainData' => $mainData,
            'processLogs' => $processLogs
        ];
    }
}
