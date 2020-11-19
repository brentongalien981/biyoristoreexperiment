<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert(['email' => "bill@bs.com", 'password' => Hash::make('bill123')]);
        DB::table('users')->insert(['email' => "steve@bs.com", 'password' => Hash::make('steve123')]);
        DB::table('users')->insert(['email' => "elon@bs.com", 'password' => Hash::make('elon123')]);
        DB::table('users')->insert(['email' => "tmac@bs.com", 'password' => Hash::make('crazy64waSu')]);
    }
}
