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
        DB::table('product_seller')->insert(['product_id' => 1, 'seller_id' => 2, 'buy_price' => 1099, 'sell_price' => 1200, 'restock_days' => 5, 'quantity' => 21, 'is_shipping_waived' => 1]);
        DB::table('product_seller')->insert(['product_id' => 2, 'seller_id' => 2, 'buy_price' => 2099, 'sell_price' => 2049.99, 'restock_days' => 5, 'quantity' => 7]);

        DB::table('product_seller')->insert(['product_id' => 4, 'seller_id' => 5, 'buy_price' => 994.99, 'sell_price' => 1000, 'restock_days' => 7, 'quantity' => 2]);
        DB::table('product_seller')->insert(['product_id' => 4, 'seller_id' => 6, 'buy_price' => 999, 'sell_price' => 1050, 'restock_days' => 3, 'quantity' => 8, 'discount_sell_price' => 1030]);
    }
}
