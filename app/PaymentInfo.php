<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentInfo extends Model
{
    public static function getRandomType() {
        $types = [
            "Visa", "Mastercard", "Amex", "PayPal", "BitCoin"
        ];

        $x = rand(0, count($types)-1);

        return $types[$x];
    }
}
