<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;
use App\OrderReturnItemStatus;

$factory->define(Model::class, function (Faker $faker) {
    return [
        'order_return_id' => null,
        'order_item_id' => null,
        'seller_product_id' => null,
        'size_availability_id' => null,
        'status_code' => OrderReturnItemStatus::getCodeByName('DEFAULT'),
        'price' => null,
        'quantity' => null,
        'created_at' => now(),
        'updated_at' => now()
    ];
});
