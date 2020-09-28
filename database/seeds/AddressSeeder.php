<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('addresses')->insert(['user_id' => 1, 'street' => "78 Monkhouse Rd", 'city' => 'Markham', 'province' => 'ON', 'country' => 'Canada', 'postal_code' => 'L6E 1V5']);
        DB::table('addresses')->insert(['user_id' => 1, 'street' => "210 Lesmill Rd", 'city' => 'North York', 'province' => 'Ontario', 'country' => 'Canada', 'postal_code' => 'M3B 2T5']);
    }
}
