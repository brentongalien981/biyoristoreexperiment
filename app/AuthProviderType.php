<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuthProviderType extends Model
{
    public const BMD = 1;
    public const GOOGLE = 2;
    public const FACEBOOK = 3;


    public static function getProviderTypeId($providerName)
    {
        switch ($providerName) {
            case 'bmd':
                return self::BMD;
            case 'google':
                return self::GOOGLE;
            case 'facebook':
                return self::FACEBOOK;
            default:
                return self::BMD;
        }
    }
}
