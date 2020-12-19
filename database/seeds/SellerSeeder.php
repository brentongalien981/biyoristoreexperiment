<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sellers')->insert(['name' => "ASB Inc.", 'alternate_name' => '', 'website' => 'https://www.biyoristore.com']);
        DB::table('sellers')->insert(['name' => "Apple", 'alternate_name' => '', 'website' => '']);
        DB::table('sellers')->insert(['name' => "NBA Store", 'alternate_name' => '', 'website' => '']);
        DB::table('sellers')->insert(['name' => "Fanatics", 'alternate_name' => '', 'website' => 'https://www.fanatics.com']);
        DB::table('sellers')->insert(['name' => "Microsoft", 'alternate_name' => '', 'website' => '']);
        DB::table('sellers')->insert(['name' => "BestBuy", 'alternate_name' => '', 'website' => '']);
    }
}
