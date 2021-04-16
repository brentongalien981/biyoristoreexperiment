<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CartItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('cart_items')->insert(['cart_id' => 2, 'product_id' => 1, 'quantity' => 1]);
        // DB::table('cart_items')->insert(['cart_id' => 2, 'product_id' => 3, 'quantity' => 3]);

        // DB::table('cart_items')->insert(['cart_id' => 3, 'product_id' => 2, 'quantity' => 1]);
    }
}
