<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\Order;
use App\OrderReturnStatus;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Model::class, function (Faker $faker) {
    $o = Order::factory()->create();

    return [
        'id' => Str::uuid()->toString(),
        'order_id' => $o->id,
        'status_code' => OrderReturnStatus::getCodeByName('DEFAULT'),
        'first_name' => $this->faker->firstName(),
        'last_name' => $this->faker->lastName(),

        'street' => $this->faker->streetAddress(),
        'city' => $this->faker->city(),
        'province' => $this->faker->state(),
        'country' => 'US',
        'postal_code' => $this->faker->postcode(),
        'phone' => '888-8888',
        'email' => $this->faker->email(),
        'created_at' => now(),
        'updated_at' => now()
    ];
});
