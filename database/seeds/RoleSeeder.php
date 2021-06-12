<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert(['name' => 'UserManager']);
        DB::table('roles')->insert(['name' => 'OrderManager']);
        DB::table('roles')->insert(['name' => 'PurchaseManager']);
        DB::table('roles')->insert(['name' => 'InventoryManager']);
    }
}
