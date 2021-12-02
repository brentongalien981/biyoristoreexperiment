<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\Order;
use App\SellerProduct;
use App\SizeAvailability;
use Faker\Generator as Faker;

$factory->define(Model::class, function (Faker $faker) {
    $allSizeAvailabilitiesCount = SizeAvailability::all()->count();
    $randSizeAvailId = rand(1, $allSizeAvailabilitiesCount);
    $randSizeAvail = SizeAvailability::find($randSizeAvailId);

    $sp = SellerProduct::find($randSizeAvail->seller_product_id);


    return [
        'order_id' => Order::factory(),
        'product_id' => $sp->product_id,
        'product_seller_id' => $sp->id,
        'size_availability_id' => $randSizeAvail->id,
        'price' => $sp->sell_price,
        'quantity' => rand(1, 3),
        'status_code' => 300 // DEFAULT
    ];
});
