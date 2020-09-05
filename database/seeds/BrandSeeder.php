<?php

use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('brands')->insert(['name' => "Apple"]);
        DB::table('brands')->insert(['name' => "Microsoft"]);
        DB::table('brands')->insert(['name' => "Samsung"]);
        DB::table('brands')->insert(['name' => "Google"]);
        DB::table('brands')->insert(['name' => "Dell"]);
        DB::table('brands')->insert(['name' => "Sony"]);
        DB::table('brands')->insert(['name' => "HP"]);
        DB::table('brands')->insert(['name' => "Toshiba"]);
        DB::table('brands')->insert(['name' => "Acer"]);
        DB::table('brands')->insert(['name' => "ASUS"]);
    }
}
