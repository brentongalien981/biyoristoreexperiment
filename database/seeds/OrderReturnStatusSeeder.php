<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderReturnStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('order_return_statuses')->insert(['code' => -302, 'name' => 'RETURN_ITEMS_INCOMPLETELY_RETURNED']);
        DB::table('order_return_statuses')->insert(['code' => -301, 'name' => 'NOT_ACCEPTABLE_PACKAGE_CONDITION_FOR_REFUND']);

        DB::table('order_return_statuses')->insert(['code' => 300, 'name' => 'DEFAULT']);
        DB::table('order_return_statuses')->insert(['code' => 301, 'name' => 'TO_BE_PICKED_UP_BY_CARRIER']);
        DB::table('order_return_statuses')->insert(['code' => 302, 'name' => 'PICKED_UP_BY_CARRIER']);
        DB::table('order_return_statuses')->insert(['code' => 303, 'name' => 'BEING_SHIPPED_FOR_RETURN']);
        DB::table('order_return_statuses')->insert(['code' => 304, 'name' => 'PACKAGE_RECEIVED']);
        DB::table('order_return_statuses')->insert(['code' => 305, 'name' => 'BEING_EVALUATED_FOR_REFUND']);
        DB::table('order_return_statuses')->insert(['code' => 306, 'name' => 'TO_BE_REFUNDED']);
        DB::table('order_return_statuses')->insert(['code' => 307, 'name' => 'BEING_REFUNDED']);
        DB::table('order_return_statuses')->insert(['code' => 308, 'name' => 'REFUNDED']);
        DB::table('order_return_statuses')->insert(['code' => 309, 'name' => 'FINALIZED']);
    }
}
