<?php

use App\Product;
use App\PackageItemType;
use Illuminate\Database\Seeder;

class AddPredefinedWeightToProducts extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = Product::all();
        $numOfItemTypes = count(PackageItemType::all());

        foreach ($products as $p) {
            $itemTypeId = rand(1, $numOfItemTypes);
            $p->weight = self::getPredefinedWeight($itemTypeId);
            $p->save();
        }
    }



    private static function getPredefinedWeight($itemTypeId)
    {
        switch ($itemTypeId) {
            case 1: // shirt
                return 6.5;
            case 2: // jersey
                return 16.0;
            case 3: // shorts
                return 10.5;
            case 4: // hoodie
                return 28.5;
            case 5: // shoes
                return 65.5;
            case 6: // pctowercase
                return 650.0;
        }
    }
}
