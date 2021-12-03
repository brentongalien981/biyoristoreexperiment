<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderReturnItemStatus extends Model
{
    public static function getCodeByName($name) {
        return self::where('name', $name)->get()[0]->code;
    }
}
