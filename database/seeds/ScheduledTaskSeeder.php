<?php

use App\ScheduledTaskStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduledTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $availableStatus = ScheduledTaskStatus::where('name', 'AVAILABLE')->get()[0];

        
        DB::table('scheduled_tasks')->insert(['command_signature' => 'BmdPurchases:Prepare', 'status_code' => $availableStatus->code, 'description' => '']);
    }
}
