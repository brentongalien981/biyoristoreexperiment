<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Brand;
use App\Model;
use App\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {

    $i = rand(1, count(Brand::all()));
    $brand = Brand::find($i);

    return [
        'name' => $brand->name . " - Product " . $faker->randomDigit,
        'brand_id' => $brand->id,
        'description' => $faker->text(1024),
        'price' => $faker->randomFloat(2, 0, 5000),
    ];
});
