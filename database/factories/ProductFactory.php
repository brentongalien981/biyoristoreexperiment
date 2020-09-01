<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'name' => $faker->company . " -- Product " . $faker->randomDigit,
        'description' => $faker->text(1024),
        'price' => $faker->randomFloat(2, 0, 5000),
    ];
});
