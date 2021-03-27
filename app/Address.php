<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use App\Http\Resources\AddressResource;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
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


            Cache::store('redisprimary')->put($cacheKey, $mainData, now()->addDays(30));
            $processLogs[] = 'has just saved mainData to cache';
        }


        return [
            'mainData' => $mainData,
            'processLogs' => $processLogs
        ];
    }
}
