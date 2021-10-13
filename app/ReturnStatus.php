<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReturnStatus extends Model
{
    public static function getIdByName($name) {
        return self::where('name', $name)->get()[0]->id ?? null;
    }



    public static function getCodeByName($name) {
        return self::where('name', $name)->get()[0]->code ?? null;
    }
}
