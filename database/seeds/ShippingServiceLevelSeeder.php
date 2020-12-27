<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingServiceLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('shipping_service_levels')->insert(['carrier' => 'ups', 'name' => 'Ground', 'latest_delivery_days' => 5]);
        DB::table('shipping_service_levels')->insert(['carrier' => 'ups', 'name' => 'UPSStandard', 'latest_delivery_days' => 5]);

        DB::table('shipping_service_levels')->insert(['carrier' => 'ups', 'name' => 'UPSSaver', 'latest_delivery_days' => 4]);
        DB::table('shipping_service_levels')->insert(['carrier' => 'ups', 'name' => 'Express', 'latest_delivery_days' => 3]);
        DB::table('shipping_service_levels')->insert(['carrier' => 'ups', 'name' => 'ExpressPlus', 'latest_delivery_days' => 2]);
        DB::table('shipping_service_levels')->insert(['carrier' => 'ups', 'name' => 'Expedited', 'latest_delivery_days' => 5]);
        DB::table('shipping_service_levels')->insert(['carrier' => 'ups', 'name' => 'NextDayAir', 'latest_delivery_days' => 1]);
        DB::table('shipping_service_levels')->insert(['carrier' => 'ups', 'name' => 'NextDayAirSaver', 'latest_delivery_days' => 1]);
        DB::table('shipping_service_levels')->insert(['carrier' => 'ups', 'name' => 'NextDayAirEarlyAM', 'latest_delivery_days' => 1]);
        DB::table('shipping_service_levels')->insert(['carrier' => 'ups', 'name' => '2ndDayAir', 'latest_delivery_days' => 2]);
        DB::table('shipping_service_levels')->insert(['carrier' => 'ups', 'name' => '2ndDayAirAM', 'latest_delivery_days' => 2]);
        DB::table('shipping_service_levels')->insert(['carrier' => 'ups', 'name' => '3DaySelect', 'latest_delivery_days' => 3]);
    }
}
