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
        DB::table('product_seller')->insert(['product_id' => 1, 'seller_id' => 2, 'buy_price' => 1099, 'sell_price' => 1200, 'quantity' => 21, 'is_shipping_waived' => 1]);
        DB::table('product_seller')->insert(['product_id' => 2, 'seller_id' => 2, 'buy_price' => 2099, 'sell_price' => 2049.99, 'quantity' => 7]);

        DB::table('product_seller')->insert(['product_id' => 4, 'seller_id' => 5, 'buy_price' => 994.99, 'sell_price' => 1000, 'quantity' => 2]);
        DB::table('product_seller')->insert(['product_id' => 4, 'seller_id' => 6, 'buy_price' => 999, 'sell_price' => 1000, 'quantity' => 8]);
    }
}
