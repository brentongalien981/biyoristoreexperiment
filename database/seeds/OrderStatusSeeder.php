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
        DB::table('order_statuses')->insert(['code' => -8001, 'name' => 'INVALID_CART', 'readable_name' => 'Invalid Cart']);
        DB::table('order_statuses')->insert(['code' => -8002, 'name' => 'CART_HAS_NO_ITEM', 'readable_name' => 'Cart Has No Item']);
        DB::table('order_statuses')->insert(['code' => -8003, 'name' => 'INVALID_PAYMENT_METHOD', 'readable_name' => 'Invalid Payment Method']);
        DB::table('order_statuses')->insert(['code' => -8004, 'name' => 'ORDER_FINALIZATION_FAILED', 'readable_name' => 'Order Finalization Failed']);

        DB::table('order_statuses')->insert(['code' => 7000, 'name' => 'WAITING_FOR_PAYMENT', 'readable_name' => 'Waiting for Payment']);
        DB::table('order_statuses')->insert(['code' => 7001, 'name' => 'PAYMENT_METHOD_CHARGED', 'readable_name' => 'Payment Method Charged']);

        DB::table('order_statuses')->insert(['code' => 8000, 'name' => 'START_OF_FINALIZING_ORDER', 'readable_name' => 'Start of Finalizing Order']);
        DB::table('order_statuses')->insert(['code' => 8001, 'name' => 'VALID_CART', 'readable_name' => 'Valid Cart']);
        DB::table('order_statuses')->insert(['code' => 8002, 'name' => 'CART_HAS_ITEM', 'readable_name' => 'Cart Has item']);
        DB::table('order_statuses')->insert(['code' => 8003, 'name' => 'CART_CHECKEDOUT_OK', 'readable_name' => 'Cart Checked-out OK']);

        DB::table('order_statuses')->insert(['code' => 8006, 'name' => 'ORDER_CREATED', 'readable_name' => 'Order Created']);
        DB::table('order_statuses')->insert(['code' => 8007, 'name' => 'ORDER_ITEMS_CREATED', 'readable_name' => 'Order Items Created']);
        DB::table('order_statuses')->insert(['code' => 8008, 'name' => 'INVENTORY_QUANTITIES_UPDATED', 'readable_name' => 'Inventory Quantities Updated']);
        DB::table('order_statuses')->insert(['code' => 8009, 'name' => 'INVENTORY_ORDER_LIMITS_UPDATED', 'readable_name' => 'Inventory Order Limits Updated']);
        DB::table('order_statuses')->insert(['code' => 8010, 'name' => 'CACHE_CART_RESET_OK', 'readable_name' => 'Cache Cart Has Been Reset OK']);
        DB::table('order_statuses')->insert(['code' => 8011, 'name' => 'ORDER_BEING_PROCESSED', 'readable_name' => 'Order Being Processed']);

        DB::table('order_statuses')->insert(['code' => 8012, 'name' => 'PROCESSING_FOR_SHIPMENT', 'readable_name' => 'Processing for Shipment']);
        DB::table('order_statuses')->insert(['code' => 8013, 'name' => 'BEING_SHIPPED', 'readable_name' => 'Order Being Shipped']);
        DB::table('order_statuses')->insert(['code' => 8014, 'name' => 'DELIVERED', 'readable_name' => 'Order Delivered']);
        DB::table('order_statuses')->insert(['code' => 8015, 'name' => 'FINALIZED', 'readable_name' => 'Finalized']);


        DB::table('order_statuses')->insert(['code' => 666001, 'name' => 'CANCELLED', 'readable_name' => 'Order Cancelled']);
        DB::table('order_statuses')->insert(['code' => 666002, 'name' => 'ORDER_APPLIED_FOR_REFUND', 'readable_name' => 'Order Applied For Refund']);
        DB::table('order_statuses')->insert(['code' => 666003, 'name' => 'ORDER_TO_BE_PICKED_UP_BY_CARRIER_FOR_REFUND', 'readable_name' => 'Ordrer to be Picked-up by Carrier for Refund']);
        DB::table('order_statuses')->insert(['code' => 666004, 'name' => 'ORDER_BEING_RETURNED_FOR_REFUND', 'readable_name' => 'Order Being Returned for Refund']);
        DB::table('order_statuses')->insert(['code' => 666005, 'name' => 'RETURNED', 'readable_name' => 'Order Returned']);
        DB::table('order_statuses')->insert(['code' => 666006, 'name' => 'PROCESSING_FOR_REFUND', 'readable_name' => 'Processing for Refund']);
        DB::table('order_statuses')->insert(['code' => 666007, 'name' => 'REFUNDED', 'readable_name' => 'Refunded']);
        DB::table('order_statuses')->insert(['code' => 666008, 'name' => 'CLOSED', 'readable_name' => 'Closed']);
        
    }
}
