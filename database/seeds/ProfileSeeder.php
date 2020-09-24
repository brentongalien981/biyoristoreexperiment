<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('profiles')->insert(['user_id' => 1, 'first_name' => "Bill", 'last_name' => "Gates", 'phone' => "000-000-0000"]);
        DB::table('profiles')->insert(['user_id' => 2, 'first_name' => "Steve", 'last_name' => "", 'phone' => "7777777777"]);
        DB::table('profiles')->insert(['user_id' => 3, 'first_name' => "el0n"]);
    }
}
