<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('teams')->insert(['name' => 'Brooklyn Nets', 'code_name' => 'BKN', 'short_name' => 'Nets']);
        DB::table('teams')->insert(['name' => 'Dallas Mavericks', 'code_name' => 'DAL', 'short_name' => 'Mavs']);
        DB::table('teams')->insert(['name' => 'Denver Nuggets', 'code_name' => 'DEN', 'short_name' => 'Nuggets']);
        DB::table('teams')->insert(['name' => 'Golden State Warriors', 'code_name' => 'GSW', 'short_name' => 'Warriors']);
        DB::table('teams')->insert(['name' => 'Los Angeles Clippers']);
        DB::table('teams')->insert(['name' => 'Los Angeles Lakers']);
        DB::table('teams')->insert(['name' => 'Miami Heat']);
        DB::table('teams')->insert(['name' => 'Milwaukee Bucks']);
        DB::table('teams')->insert(['name' => 'New Orleans Pelicans']);
        DB::table('teams')->insert(['name' => 'Philadelphia 76ers']);
        DB::table('teams')->insert(['name' => 'Portland Trail Blazers']);
        DB::table('teams')->insert(['name' => 'Toronto Raptors']);
        DB::table('teams')->insert(['name' => 'Utah Jazz']);
        DB::table('teams')->insert(['name' => 'Washington Wizards']);
        
    }
}
