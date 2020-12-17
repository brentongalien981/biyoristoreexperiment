<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('product_seller')->insert(['product_id' => 1, 'seller_id' => 2, 'buy_price' => 1099, 'sell_price' => 1200, 'quantity' => 21]);
        DB::table('product_seller')->insert(['product_id' => 2, 'seller_id' => 2, 'buy_price' => 2099, 'sell_price' => 2049.99, 'quantity' => 7]);
    }
}
