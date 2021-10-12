<?php

namespace App\Http\BmdHttpResponseCodes;

use Exception;



class JoinHttpResponseCodes
{
    public const INVALID_RESET_TOKEN = ['code' => 'INVALID_RESET_TOKEN', 'message' => 'INVALID_RESET_TOKEN', 'readableMessage' => 'Invalid Reset Token'];
    public const EXPIRED_RESET_TOKEN = ['code' => 'EXPIRED_RESET_TOKEN', 'message' => 'EXPIRED_RESET_TOKEN', 'readableMessage' => 'Expired Reset Token'];
    public const USER_NOT_FOUND = ['code' => 'USER_NOT_FOUND', 'message' => 'USER_NOT_FOUND', 'readableMessage' => 'User not found.'];

}