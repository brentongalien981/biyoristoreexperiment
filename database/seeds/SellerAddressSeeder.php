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
        DB::table('seller_addresses')->insert(['seller_id' => 5, 'street' => "One Microsoft Way", 'city' => 'Redmond', 'province' => 'WA', 'country' => 'United States', 'postal_code' => '98052']);
        DB::table('seller_addresses')->insert(['seller_id' => 6, 'street' => "7601 Penn Avenue South", 'city' => 'Richfield', 'province' => 'MN', 'country' => 'United States', 'postal_code' => '55423']);
    }
}
