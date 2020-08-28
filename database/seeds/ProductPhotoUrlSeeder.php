<?php

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductPhotoUrlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('product_photo_urls')->insert(['product_id' => 1, 'url' => "iphoneX-portrait.jpg"]);
        DB::table('product_photo_urls')->insert(['product_id' => 1, 'url' => "iphoneX-landscape.jpg"]);

        DB::table('product_photo_urls')->insert(['product_id' => 2, 'url' => "imac1.jpg"]);
        DB::table('product_photo_urls')->insert(['product_id' => 2, 'url' => "imac2.jpg"]);
        DB::table('product_photo_urls')->insert(['product_id' => 2, 'url' => "imac3.jpg"]);

        DB::table('product_photo_urls')->insert(['product_id' => 3, 'url' => "asus1.jpg"]);
        DB::table('product_photo_urls')->insert(['product_id' => 3, 'url' => "asus2.jpg"]);
    }
}
