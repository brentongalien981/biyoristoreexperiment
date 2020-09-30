<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('carts')->insert(['user_id' => 1, 'is_active' => false]);
        DB::table('carts')->insert(['user_id' => 1, 'is_active' => true]);
        DB::table('carts')->insert(['user_id' => 3, 'is_active' => true]);
    }
}
