<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class OrderStatus extends Model
{

    public static function getIdByName($name) {
        return self::where('name', $name)->get()[0]->id;
    }


}
