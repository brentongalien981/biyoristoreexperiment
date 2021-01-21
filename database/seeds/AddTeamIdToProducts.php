<?php

use App\Team;
use App\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddTeamIdToProducts extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = Product::all();
        $numOfTeams = count(Team::all());

        foreach ($products as $p) {
            $teamId = rand(1, $numOfTeams);
            $p->team_id = $teamId;
            $p->save();
        }
    }
}
