<?php

use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert(['name' => "laptop"]);
        DB::table('categories')->insert(['name' => "tablet"]);
        DB::table('categories')->insert(['name' => "phone"]);
        DB::table('categories')->insert(['name' => "phablet"]);
        DB::table('categories')->insert(['name' => "3 in 1"]);
    }
}
