<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('exchange_rates')->insert(['from' => 'CAD', 'to' => 'USD', 'rate' => 0.83]);
        DB::table('exchange_rates')->insert(['from' => 'USD', 'to' => 'CAD', 'rate' => 1.21]);
        DB::table('exchange_rates')->insert(['from' => 'USD', 'to' => 'PHP', 'rate' => 47.80]);
        DB::table('exchange_rates')->insert(['from' => 'CAD', 'to' => 'PHP', 'rate' => 39.60]);
        DB::table('exchange_rates')->insert(['from' => 'PHP', 'to' => 'USD', 'rate' => 0.021]);
        DB::table('exchange_rates')->insert(['from' => 'PHP', 'to' => 'CAD', 'rate' => 0.025]);
    }
}
