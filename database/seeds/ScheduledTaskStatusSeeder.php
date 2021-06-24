<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduledTaskStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('scheduled_task_statuses')->insert(['code' => 'STS-1000', 'name' => 'AVAILABLE', 'readable_name' => 'Available']);
        DB::table('scheduled_task_statuses')->insert(['code' => 'STS-1001', 'name' => 'PROCESSING', 'readable_name' => 'Processing']);

        DB::table('scheduled_task_statuses')->insert(['code' => 'STS-1002', 'name' => 'PROCESS_SUCCEEDED', 'readable_name' => 'Process Succeeded']);


        DB::table('scheduled_task_statuses')->insert(['code' => 'STS-6001', 'name' => 'PREVIOUS_DISPATCH_STILL_PROCESSING', 'readable_name' => 'Previous Dispatch Still Processing']);
        DB::table('scheduled_task_statuses')->insert(['code' => 'STS-6002', 'name' => 'PROCESS_FAILED', 'readable_name' => 'Process Failed']);
    }
}
