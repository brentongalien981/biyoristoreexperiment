<?php

namespace App\BmdHelpers;

use App\BmdAuth;
use App\Models\BmdAuthUser;


class BmdAuthProvider
{

    private static $instance = null;
    private static $user = null;

    
    private function __construct()
    {
        // PRIVATE
    }



    public static function setInstance($token, $authProviderId)
    {
        if (!self::$instance) {
            $possibleAccounts = BmdAuth::where('token', $token)->where('auth_provider_type_id', $authProviderId)->get();

            if (isset($possibleAccounts) && count($possibleAccounts) === 1 && isset($possibleAccounts[0])) {
                self::$instance = $possibleAccounts[0];
            }
        }
    }



    public static function getInstance() {
        return self::$instance;
    }



    public static function check() {
        if (isset(self::$instance)) { return true; }
        return false;
    }


    public static function user()
    {
        // return $this->hasOne(User::class, 'id', 'user_id');
        return self::$instance->user;
    }
}
