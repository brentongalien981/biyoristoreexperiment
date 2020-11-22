<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('order_statuses')->insert(['name' => 'INVALID CART']);
        DB::table('order_statuses')->insert(['name' => 'VALID CART']);
        DB::table('order_statuses')->insert(['name' => 'CART HAS ITEM']);
        DB::table('order_statuses')->insert(['name' => 'CART HAS NO ITEM']);
        DB::table('order_statuses')->insert(['name' => 'WAITING FOR PAYMENT']);
        DB::table('order_statuses')->insert(['name' => 'PAYMENT METHOD CHARGED']);
        DB::table('order_statuses')->insert(['name' => 'CART CHECKEDOUT OK']);
        DB::table('order_statuses')->insert(['name' => 'CANCELLED']);
        DB::table('order_statuses')->insert(['name' => 'ORDER CREATED']);
        DB::table('order_statuses')->insert(['name' => 'ORDER ITEMS_CREATED']);
        DB::table('order_statuses')->insert(['name' => 'PROCESSING FOR SHIPMENT']);
        DB::table('order_statuses')->insert(['name' => 'BEING SHIPPED']);
        DB::table('order_statuses')->insert(['name' => 'DELIVERED']);
        DB::table('order_statuses')->insert(['name' => 'SHIPPED FOR REFUND']);
        DB::table('order_statuses')->insert(['name' => 'RETURNED']);
        DB::table('order_statuses')->insert(['name' => 'PROCESSING FOR REFUND']);
        DB::table('order_statuses')->insert(['name' => 'REFUNDED']);
    }
}
