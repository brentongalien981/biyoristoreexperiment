<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StripeCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('stripe_customers')->insert(['user_id' => 1, 'stripe_customer_id' => 'cus_IJ6W6skPk8w6ws']);
        DB::table('stripe_customers')->insert(['user_id' => 2, 'stripe_customer_id' => 'cus_IJ6ZxQXrTDwSBh']);
        DB::table('stripe_customers')->insert(['user_id' => 3, 'stripe_customer_id' => 'cus_IJ6ZdlEBVRmKEK']);
    }
}
