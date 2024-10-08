<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderItemStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('order_item_status')->insert(['code' => -304, 'name' => 'PURCHASE_INCOMPLETELY_RECEIVED']);
        DB::table('order_item_status')->insert(['code' => -301, 'name' => 'EVALUATED_INCOMPLETELY_FOR_PURCHASE']);

        DB::table('order_item_status')->insert(['code' => -310, 'name' => 'MISSING_ORDER_ITEM']);
        DB::table('order_item_status')->insert(['code' => -311, 'name' => 'BROKEN_ORDER_ITEM']);
        DB::table('order_item_status')->insert(['code' => -312, 'name' => 'TOO_LATE_TO_DELIVER']);
        DB::table('order_item_status')->insert(['code' => -313, 'name' => 'TOO_EXPENSIVE_TO_DELIVER']);
        DB::table('order_item_status')->insert(['code' => -314, 'name' => 'OTHER_ORDER_ITEM_PROBLEMS']);

        


        
        DB::table('order_item_status')->insert(['code' => 300, 'name' => 'DEFAULT']);
        DB::table('order_item_status')->insert(['code' => 301, 'name' => 'TO_BE_PURCHASED']);
        DB::table('order_item_status')->insert(['code' => 302, 'name' => 'PURCHASED']);
        DB::table('order_item_status')->insert(['code' => 303, 'name' => 'TO_BE_PURCHASE_RECEIVED']);
        DB::table('order_item_status')->insert(['code' => 304, 'name' => 'PURCHASE_RECEIVED']);
        DB::table('order_item_status')->insert(['code' => 305, 'name' => 'IN_STOCK']);

        DB::table('order_item_status')->insert(['code' => 306, 'name' => 'TO_BE_PACKAGED']);
        DB::table('order_item_status')->insert(['code' => 310, 'name' => 'BEING_PACKAGED']);
        DB::table('order_item_status')->insert(['code' => 307, 'name' => 'PACKAGED']);

        DB::table('order_item_status')->insert(['code' => 308, 'name' => 'TO_BE_DISPATCHED']);
        DB::table('order_item_status')->insert(['code' => 309, 'name' => 'DISPATCHED']); 
    }
}
