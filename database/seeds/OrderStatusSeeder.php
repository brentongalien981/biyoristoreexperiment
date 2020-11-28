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
        DB::table('order_statuses')->insert(['name' => 'INVALID_CART', 'readable_name' => 'Invalid Cart']);
        DB::table('order_statuses')->insert(['name' => 'VALID_CART', 'readable_name' => 'Valid Cart']);
        DB::table('order_statuses')->insert(['name' => 'CART_HAS_ITEM', 'readable_name' => 'Cart Has item']);
        DB::table('order_statuses')->insert(['name' => 'CART_HAS_NO_ITEM', 'readable_name' => 'Cart Has No Item']);
        DB::table('order_statuses')->insert(['name' => 'INVALID_PAYMENT_METHOD', 'readable_name' => 'Invalid Payment Method']);
        DB::table('order_statuses')->insert(['name' => 'WAITING_FOR_PAYMENT', 'readable_name' => 'Waiting for Payment']);
        DB::table('order_statuses')->insert(['name' => 'PAYMENT_METHOD_CHARGED', 'readable_name' => 'Payment Method Charged']);
        DB::table('order_statuses')->insert(['name' => 'CART_CHECKEDOUT_OK', 'readable_name' => 'Cart Checked-out OK']);
        DB::table('order_statuses')->insert(['name' => 'CANCELLED', 'readable_name' => 'Order Cancelled']);
        DB::table('order_statuses')->insert(['name' => 'ORDER_CREATED', 'readable_name' => 'Order Created']);
        DB::table('order_statuses')->insert(['name' => 'ORDER_ITEMS_CREATED', 'readable_name' => 'Order Items Created']);
        DB::table('order_statuses')->insert(['name' => 'PROCESSING_FOR_SHIPMENT', 'readable_name' => 'Processing for Shipment']);
        DB::table('order_statuses')->insert(['name' => 'BEING_SHIPPED', 'readable_name' => 'Order Being Shipped']);
        DB::table('order_statuses')->insert(['name' => 'DELIVERED', 'readable_name' => 'Order Delivered']);
        DB::table('order_statuses')->insert(['name' => 'SHIPPED_FOR_REFUND', 'readable_name' => 'Shipped for Refund']);
        DB::table('order_statuses')->insert(['name' => 'RETURNED', 'readable_name' => 'Order Returned']);
        DB::table('order_statuses')->insert(['name' => 'PROCESSING_FOR_REFUND', 'readable_name' => 'Processing for Refund']);
        DB::table('order_statuses')->insert(['name' => 'REFUNDED', 'readable_name' => 'Refunded']);
    }
}
