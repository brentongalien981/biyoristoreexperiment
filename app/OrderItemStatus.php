<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderItemStatus extends Model
{
    // BMD-ON-STAGING
    public const DEFAULT_STATUS = 300;



    public static function getIdByName($name) {
        return self::where('name', $name)->get()[0]->id ?? null;
    }



    public static function getCodeByName($name) {
        return self::where('name', $name)->get()[0]->code ?? null;
    }
}
