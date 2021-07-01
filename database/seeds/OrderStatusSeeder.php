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
        // BAD-ORDER-STATUSES
        DB::table('order_statuses')->insert(['code' => -7001, 'name' => 'PAYMENT_METHOD_NOT_CHARGED', 'readable_name' => 'Payment Method Not Charged']);


        DB::table('order_statuses')->insert(['code' => -8001, 'name' => 'INVALID_CART', 'readable_name' => 'Invalid Cart']);
        DB::table('order_statuses')->insert(['code' => -8002, 'name' => 'CART_HAS_NO_ITEM', 'readable_name' => 'Cart Has No Item']);
        DB::table('order_statuses')->insert(['code' => -8003, 'name' => 'INVALID_PAYMENT_METHOD', 'readable_name' => 'Invalid Payment Method']);
        DB::table('order_statuses')->insert(['code' => -8004, 'name' => 'ORDER_FINALIZATION_FAILED', 'readable_name' => 'Order Finalization Failed']);
        DB::table('order_statuses')->insert(['code' => -8005, 'name' => 'ORDER_FINALIZATION_EXCEPTION', 'readable_name' => 'Order Finalization Exception']);
        DB::table('order_statuses')->insert(['code' => -8006, 'name' => 'ORDER_FINALIZATION_INCOMPLETE', 'readable_name' => 'Order Finalization Incomplete']);

        
        // PAYMENT-RECEIVED
        DB::table('order_statuses')->insert(['code' => 7000, 'name' => 'WAITING_FOR_PAYMENT', 'readable_name' => 'Waiting for Payment']);
        DB::table('order_statuses')->insert(['code' => 7001, 'name' => 'PAYMENT_METHOD_VALIDATED', 'readable_name' => 'Payment Method Validated']);
        DB::table('order_statuses')->insert(['code' => 7002, 'name' => 'PAYMENT_METHOD_CHARGED', 'readable_name' => 'Payment Method Charged']);
        

        DB::table('order_statuses')->insert(['code' => 8000, 'name' => 'START_OF_FINALIZING_ORDER', 'readable_name' => 'Start of Finalizing Order']);
        DB::table('order_statuses')->insert(['code' => 8001, 'name' => 'VALID_CART', 'readable_name' => 'Valid Cart']);
        DB::table('order_statuses')->insert(['code' => 8002, 'name' => 'CART_HAS_ITEM', 'readable_name' => 'Cart Has item']);
        DB::table('order_statuses')->insert(['code' => 8003, 'name' => 'CART_CHECKEDOUT_OK', 'readable_name' => 'Cart Checked-out OK']);
        

        // ORDER-CONFIRMED
        DB::table('order_statuses')->insert(['code' => 8006, 'name' => 'ORDER_CREATED', 'readable_name' => 'Order Created']);
        DB::table('order_statuses')->insert(['code' => 8007, 'name' => 'ORDER_ITEMS_CREATED', 'readable_name' => 'Order Items Created']);
        DB::table('order_statuses')->insert(['code' => 8008, 'name' => 'INVENTORY_QUANTITIES_UPDATED', 'readable_name' => 'Inventory Quantities Updated']);
        DB::table('order_statuses')->insert(['code' => 8009, 'name' => 'INVENTORY_ORDER_LIMITS_UPDATED', 'readable_name' => 'Inventory Order Limits Updated']);
        DB::table('order_statuses')->insert(['code' => 8010, 'name' => 'CACHE_CART_RESET_OK', 'readable_name' => 'Cache Cart Has Been Reset OK']);
        DB::table('order_statuses')->insert(['code' => 8011, 'name' => 'ORDER_CONFIRMED', 'readable_name' => 'Order Confirmed']);
        DB::table('order_statuses')->insert(['code' => 8012, 'name' => 'ORDER_DETAILS_EMAILED_TO_USER', 'readable_name' => 'Order Details Emailed To User']);


        // ORDER-PROCESSING
        DB::table('order_statuses')->insert(['code' => 8013, 'name' => 'PROCESSING_FOR_SHIPMENT', 'readable_name' => 'Processing for Shipment']);
        DB::table('order_statuses')->insert(['code' => 8014, 'name' => 'BEING_SHIPPED', 'readable_name' => 'Order Being Shipped']);

        // ORDER-DELIVERED
        DB::table('order_statuses')->insert(['code' => 8015, 'name' => 'DELIVERED', 'readable_name' => 'Order Delivered']);
        DB::table('order_statuses')->insert(['code' => 8016, 'name' => 'FINALIZED', 'readable_name' => 'Finalized']);


        // INTERNAL-POSSIBLE-ERROR
        DB::table('order_statuses')->insert(['code' => 9001, 'name' => 'POSSIBLE_DOUBLE_PAYMENT', 'readable_name' => 'Possible Double Payment']);
        DB::table('order_statuses')->insert(['code' => 9002, 'name' => 'MISSING_STRIPE_PAYMENT_INTENT_LINK', 'readable_name' => 'Missing Stripe Payment-Intent Link']);
        DB::table('order_statuses')->insert(['code' => 9003, 'name' => 'CUSTOMER_HAS_TO_BE_REFUNDED', 'readable_name' => 'Customer Has To Be Refunded']);



        // PREDEFINED-PAYMENT-STATUSES
        DB::table('order_statuses')->insert(['code' => 7101, 'name' => 'STRIPE_PAYMENT_INTENT_CREATED', 'readable_name' => 'Stripe Payment Intent Created']);

        DB::table('order_statuses')->insert(['code' => 8100, 'name' => 'START_OF_FINALIZING_ORDER_WITH_PREDEFINED_PAYMENT', 'readable_name' => 'Start of Finalizing Order With Predefined Payment']);
        DB::table('order_statuses')->insert(['code' => 8101, 'name' => 'DB_CART_CREATED', 'readable_name' => 'Db Cart Created']);
        DB::table('order_statuses')->insert(['code' => 8102, 'name' => 'CACHE_CART_UPDATED_TO_LATEST_VERSION', 'readable_name' => 'Cache Cart Updated To Latest Version']);
        DB::table('order_statuses')->insert(['code' => 8103, 'name' => 'DB_CART_ITEMS_CREATED', 'readable_name' => 'Db Cart Items Created']);



        // ORDER-PURCHASING-and-INVENTORY-related STATUSES
        DB::table('order_statuses')->insert(['code' => -8301, 'name' => 'EVALUATED_INCOMPLETELY_FOR_PURCHASE', 'readable_name' => 'Evaluated Incompletely for Purchase']);
        DB::table('order_statuses')->insert(['code' => -8304, 'name' => 'PURCHASE_INCOMPLETELY_RECEIVED', 'readable_name' => 'Purchase Incompletely Received']);

        DB::table('order_statuses')->insert(['code' => 8300, 'name' => 'BEING_EVALUATED_FOR_PURCHASE', 'readable_name' => 'Being Evaluated for Purchase']);
        DB::table('order_statuses')->insert(['code' => 8301, 'name' => 'TO_BE_PURCHASED', 'readable_name' => 'To be Purchased']);
        DB::table('order_statuses')->insert(['code' => 8302, 'name' => 'PURCHASED', 'readable_name' => 'Purchased']);
        DB::table('order_statuses')->insert(['code' => 8303, 'name' => 'TO_BE_PURCHASE_RECEIVED', 'readable_name' => 'To be Purchase-Received']);
        DB::table('order_statuses')->insert(['code' => 8304, 'name' => 'PURCHASE_RECEIVED', 'readable_name' => 'Purchase-Received']);
        DB::table('order_statuses')->insert(['code' => 8305, 'name' => 'IN_STOCK', 'readable_name' => 'In-Stock']);
        DB::table('order_statuses')->insert(['code' => 8306, 'name' => 'TO_BE_PACKAGED', 'readable_name' => 'To be Packaged']);
        DB::table('order_statuses')->insert(['code' => 8307, 'name' => 'PACKAGED', 'readable_name' => 'Packaged']);
        DB::table('order_statuses')->insert(['code' => 8308, 'name' => 'TO_BE_DISPATCHED', 'readable_name' => 'To be Dispatched']);
        DB::table('order_statuses')->insert(['code' => 8309, 'name' => 'DISPATCHED', 'readable_name' => 'Dispatched']);
        


        // RETURN-STATUSES
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
