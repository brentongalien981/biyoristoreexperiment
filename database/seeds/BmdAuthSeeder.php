<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BmdAuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bmd_auths')->insert(['user_id' => 1, 'token' => 'xxxZZZxxx', 'expires_in' => '1619620872', 'frontend_pseudo_expires_in' => '1619620872', 'auth_provider_type_id' => 1]);
        DB::table('bmd_auths')->insert(['user_id' => 2, 'token' => 'xxxZZZxxx', 'expires_in' => '1619620872', 'frontend_pseudo_expires_in' => '1619620872', 'auth_provider_type_id' => 1]);
        DB::table('bmd_auths')->insert(['user_id' => 3, 'token' => 'xxxZZZxxx', 'expires_in' => '1619620872', 'frontend_pseudo_expires_in' => '1619620872', 'auth_provider_type_id' => 1]);
        DB::table('bmd_auths')->insert(['user_id' => 4, 'token' => 'xxxZZZxxx', 'expires_in' => '1619620872', 'frontend_pseudo_expires_in' => '1619620872', 'auth_provider_type_id' => 1]);
    }
}
