<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReturnStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('return_statuses')->insert(['code' => -402, 'name' => 'FAILED_EVALUATION_FOR_REFUND']);
        DB::table('return_statuses')->insert(['code' => -401, 'name' => 'CANCELLED']);
        DB::table('return_statuses')->insert(['code' => 400, 'name' => 'DEFAULT']);
        DB::table('return_statuses')->insert(['code' => 401, 'name' => 'BEING_UPDATED']);
        DB::table('return_statuses')->insert(['code' => 402, 'name' => 'TO_BE_PICKED_UP']);
        DB::table('return_statuses')->insert(['code' => 403, 'name' => 'BEING_SHIPPED_FOR_RETURN']);
        DB::table('return_statuses')->insert(['code' => 404, 'name' => 'RETURNED']);
        DB::table('return_statuses')->insert(['code' => 405, 'name' => 'BEING_EVALUATED_FOR_REFUND']);
        DB::table('return_statuses')->insert(['code' => 406, 'name' => 'TO_BE_REFUNDED']);
        DB::table('return_statuses')->insert(['code' => 407, 'name' => 'BEING_REFUNDED']);
        DB::table('return_statuses')->insert(['code' => 408, 'name' => 'REFUNDED']);
        DB::table('return_statuses')->insert(['code' => 409, 'name' => 'CLOSED']);

    }
}
