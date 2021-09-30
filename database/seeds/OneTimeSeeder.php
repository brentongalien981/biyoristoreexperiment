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
        DB::table('order_statuses')->insert(['code' => 8311, 'name' => 'SHIPPING_LABEL_BOUGHT', 'readable_name' => 'Shipping Label Bought']);
    }
}
