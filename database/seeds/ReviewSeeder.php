<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('reviews')->insert(['product_id' => 2, 'user_id' => 1, 'message' => 'This product is unbelievable', 'rating' => 4, 'created_at' => now()]);
    }
}
