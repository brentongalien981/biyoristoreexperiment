<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BmdAuth extends Model
{

    public const NUM_OF_SECS_PER_MONTH = 60 * 60 * 24 * 30;

    
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
