<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SizeAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('size_availabilities')->insert(['seller_product_id' => 1, 'size' => 'S', 'quantity' => '10']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 1, 'size' => 'M', 'quantity' => '8']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 1, 'size' => 'L', 'quantity' => '4']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 1, 'size' => 'XL', 'quantity' => '2']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 1, 'size' => '3XL', 'quantity' => '7']);

        DB::table('size_availabilities')->insert(['seller_product_id' => 3, 'size' => '6.0', 'quantity' => '2']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 3, 'size' => '6.5', 'quantity' => '2']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 3, 'size' => '9.0', 'quantity' => '3']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 3, 'size' => '10.0', 'quantity' => '1']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 3, 'size' => '10.5', 'quantity' => '2']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 3, 'size' => '11.0', 'quantity' => '1']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 3, 'size' => '11.5', 'quantity' => '2']);



        DB::table('size_availabilities')->insert(['seller_product_id' => 5, 'size' => 'S', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 5, 'size' => 'M', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 5, 'size' => 'L', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 5, 'size' => 'XL', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 5, 'size' => '2XL', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 5, 'size' => '3XL', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 5, 'size' => '5XL', 'quantity' => '50']);


        DB::table('size_availabilities')->insert(['seller_product_id' => 6, 'size' => 'M', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 6, 'size' => 'L', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 6, 'size' => '2XL', 'quantity' => '50']);


        DB::table('size_availabilities')->insert(['seller_product_id' => 7, 'size' => '6.0', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 7, 'size' => '7.0', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 7, 'size' => '7.5', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 7, 'size' => '8.0', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 7, 'size' => '8.5', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 7, 'size' => '9.0', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 7, 'size' => '10.0', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 7, 'size' => '10.5', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 7, 'size' => '11.0', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 7, 'size' => '11.5', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 7, 'size' => '13.0', 'quantity' => '50']);
        DB::table('size_availabilities')->insert(['seller_product_id' => 7, 'size' => '16.0', 'quantity' => '50']);
    }
}
