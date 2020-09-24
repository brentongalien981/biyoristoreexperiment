<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payment_infos')->insert(['user_id' => 1, 'type' => 'Visa', 'card_number' => '1111111111111111', 'expiration_month' => '01', 'expiration_year' => '2021']);
        DB::table('payment_infos')->insert(['user_id' => 1, 'type' => 'Mastercard', 'card_number' => '2222222222222222', 'expiration_month' => '02', 'expiration_year' => '2022']);

        DB::table('payment_infos')->insert(['user_id' => 3, 'type' => 'Mastercard', 'card_number' => '0000000000000000', 'expiration_month' => '12', 'expiration_year' => '2030']);
        DB::table('payment_infos')->insert(['user_id' => 3, 'type' => 'Visa', 'card_number' => '9999999999999999', 'expiration_month' => '11', 'expiration_year' => '2031']);
        DB::table('payment_infos')->insert(['user_id' => 3, 'type' => 'Amex', 'card_number' => '8888888888888888', 'expiration_month' => '10', 'expiration_year' => '2032']);
    }
}
