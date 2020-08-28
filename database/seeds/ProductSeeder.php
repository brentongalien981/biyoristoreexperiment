<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('products')->insert([
            'name' => "iPhone 11 Pro Max",
            'description' => "lorem ipsum",
            'price' => 1099
        ]);

        DB::table('products')->insert(['name' => "iMac Pro", 'description' => "lorem ipsum ipsum", 'price' => 2099]);
        DB::table('products')->insert(['name' => "ASUS Zen Book", 'description' => "lorem ipsum ipsum", 'price' => 999]);
    }
}
