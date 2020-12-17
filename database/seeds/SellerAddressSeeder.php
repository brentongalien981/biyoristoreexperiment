<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SellerAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('seller_addresses')->insert(['seller_id' => 2, 'street' => "10600 N Tantau Ave", 'city' => 'Cupertino', 'province' => 'CA', 'country' => 'United States', 'postal_code' => '95014']);
    }
}
