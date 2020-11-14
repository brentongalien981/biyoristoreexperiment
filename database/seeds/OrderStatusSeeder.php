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
        DB::table('order_statuses')->insert(['name' => 'WAITING FOR PAYMENT']);
        DB::table('order_statuses')->insert(['name' => 'PAID']);
        DB::table('order_statuses')->insert(['name' => 'CANCELLED']);
        DB::table('order_statuses')->insert(['name' => 'PROCESSING FOR SHIPMENT']);
        DB::table('order_statuses')->insert(['name' => 'SHIPPED']);
        DB::table('order_statuses')->insert(['name' => 'DELIVERED']);
        DB::table('order_statuses')->insert(['name' => 'SHIPPED FOR REFUND']);
        DB::table('order_statuses')->insert(['name' => 'RETURNED']);
        DB::table('order_statuses')->insert(['name' => 'PROCESSING FOR REFUND']);
        DB::table('order_statuses')->insert(['name' => 'REFUNDED']);
    }
}
