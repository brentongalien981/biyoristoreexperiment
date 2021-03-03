<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuthProviderType extends Model
{
    public const BMD = 1;
    public const GOOGLE = 2;
    public const FACEBOOK = 3;
}
