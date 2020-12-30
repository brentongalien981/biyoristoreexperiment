<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageItemTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('package_item_types')->insert(['name' => 'shirt', 'encompassing_level' => 1, 'conversion_ratio' => 50.00]);
        DB::table('package_item_types')->insert(['name' => 'jersey', 'encompassing_level' => 2, 'conversion_ratio' => 40.00]);
        DB::table('package_item_types')->insert(['name' => 'shorts', 'encompassing_level' => 3, 'conversion_ratio' => 30.00]);
        DB::table('package_item_types')->insert(['name' => 'hoodie', 'encompassing_level' => 4, 'conversion_ratio' => 12.00]);
        DB::table('package_item_types')->insert(['name' => 'shoes', 'encompassing_level' => 5, 'conversion_ratio' => 6.00]);
        DB::table('package_item_types')->insert(['name' => 'pctowercase', 'encompassing_level' => 6, 'conversion_ratio' => 1.00]);
    }
}
