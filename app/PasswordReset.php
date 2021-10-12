<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    // protected $fillable = ['email', 'token'];
    public const RESET_TOKEN_LIFESPAN_IN_SECS = 60 * 10;


    public function isExpired() {
        $timestampNow = getdate()[0];
        $tokenCreationDateTimestamp = strtotime($this->created_at);

        $elapsedSec = $timestampNow - $tokenCreationDateTimestamp;
        if ($elapsedSec > self::RESET_TOKEN_LIFESPAN_IN_SECS) { return true; }
        return false;

    }
}
