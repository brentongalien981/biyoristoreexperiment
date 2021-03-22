<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class BmdAuth extends Model
{

    public const NUM_OF_SECS_PER_MONTH = 60 * 60 * 24 * 30;



    public function getCacheKey() {
        $bmdAuthCacheRecordKey = 'bmdAuth?token=' . $this->token . '&authProviderId=' . $this->auth_provider_type_id;
        return $bmdAuthCacheRecordKey;
    }



    public function deleteOldCacheRecord() {
        $bmdAuthCacheRecordKey = 'bmdAuth?token=' . $this->token . '&authProviderId=' . $this->auth_provider_type_id;
        Cache::store('redisprimary')->forget($bmdAuthCacheRecordKey);
    }


    public function saveToCache($stayLoggedIn = false) {
        $bmdAuthCacheRecordKey = 'bmdAuth?token=' . $this->token . '&authProviderId=' . $this->auth_provider_type_id;
        $this->stayLoggedIn = $stayLoggedIn;
        Cache::store('redisprimary')->put($bmdAuthCacheRecordKey, $this, now()->addDays(30));
    }

    
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
