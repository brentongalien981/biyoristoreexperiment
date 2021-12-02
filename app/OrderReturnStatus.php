<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderReturnStatus extends Model
{
    public static function getCodeByName($name) {
        return self::where('name', $name)->get()[0]->code;
    }
}
