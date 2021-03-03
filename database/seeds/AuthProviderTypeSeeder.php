<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthProviderTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('auth_provider_types')->insert(['name' => 'BMD']);
        DB::table('auth_provider_types')->insert(['name' => 'Google']);
        DB::table('auth_provider_types')->insert(['name' => 'Facebook']);
    }
}
