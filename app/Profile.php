<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ProfileResource;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
    ];



    /** MAIN-FUNCS */
    public function user()
    {
        return $this->belongsTo('App\User');
    }



    /** HELPER-FUNCS */
    public static function saveProfileToCache($profile) {
        $processLogs = ['In CLASS: Profile, METHOD: saveProfileToCache()'];

        $cacheKey = 'profile?userId=' . $profile->user->id;
        $mainData = new ProfileResource($profile);

        Cache::store('redisprimary')->put($cacheKey, $mainData, now()->addDays(7));
        $processLogs[] = 'has just saved mainData to cache';

        return [
            'mainData' => $mainData,
            'processLogs' => $processLogs
        ];
    }



    public static function getProfileFromCacheWithUser($user) {
        $processLogs = ['In CLASS: Profile, METHOD: getProfileFromCacheWithUser()'];

        $cacheKey = 'profile?userId=' . $user->id;
        $mainData = Cache::store('redisreader')->get($cacheKey);

        if (isset($mainData)) {
            $processLogs[] = 'mainData is from cache';
        } else {

            $mainData = new ProfileResource($user->profile);
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
