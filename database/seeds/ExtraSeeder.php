<?php

use Illuminate\Database\Seeder;

class ExtraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AddPredefinedWeightToProducts::class,
            AddRandomItemTypeIdToProducts::class,
            AddTeamIdToProducts::class,
            SizeAvailabilitySeeder::class
        ]);
    }
}
