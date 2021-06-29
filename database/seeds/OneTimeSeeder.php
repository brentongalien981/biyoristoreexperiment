<?php

use App\SizeAvailability;
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
        foreach (SizeAvailability::all() as $sa) {
            $sa->daily_reset_quantity = 20;
            $sa->save();
        }
    }
}
