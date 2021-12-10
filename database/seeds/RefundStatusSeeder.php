<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefundStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('refund_statuses')->insert(['code' => -301, 'name' => 'NOT_ACCEPTABLE_FOR_REFUND']);
        DB::table('refund_statuses')->insert(['code' => 300, 'name' => 'DEFAULT']);
        DB::table('refund_statuses')->insert(['code' => 301, 'name' => 'BEING_EVALUATED_FOR_REFUND']);
        DB::table('refund_statuses')->insert(['code' => 302, 'name' => 'TO_BE_REFUNDED']);
        DB::table('refund_statuses')->insert(['code' => 303, 'name' => 'BEING_REFUNDED']);
        DB::table('refund_statuses')->insert(['code' => 304, 'name' => 'REFUNDED']);
        DB::table('refund_statuses')->insert(['code' => 305, 'name' => 'FINALIZED']);
    }
}
