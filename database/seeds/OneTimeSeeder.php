<?php

use App\SizeAvailability;
use App\ScheduledTaskStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OneTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('order_statuses')->insert(['code' => -8400, 'name' => 'MISSING_ORDER_ITEM', 'readable_name' => 'Missing Order Item']);
        DB::table('order_statuses')->insert(['code' => -8401, 'name' => 'BROKEN_ORDER_ITEM', 'readable_name' => 'Broken Order Item']);
        DB::table('order_statuses')->insert(['code' => -8402, 'name' => 'TOO_LATE_TO_DELIVER', 'readable_name' => 'Too Late To Deliver']);
        DB::table('order_statuses')->insert(['code' => -8403, 'name' => 'TOO_EXPENSIVE_TO_DELIVER', 'readable_name' => 'Too Expensive To Deliver']);
        DB::table('order_statuses')->insert(['code' => -8404, 'name' => 'OTHER_ORDER_PROBLEMS', 'readable_name' => 'Other Order Problems']);

        DB::table('order_statuses')->insert(['code' => 8310, 'name' => 'BEING_PACKAGED', 'readable_name' => 'Being Packaged']);
    }
}
