<?php

use App\Category;
use App\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = Product::all();

        foreach ($products as $p) {

            $categoryIds = $this->getRandomCategoryIds();
            
            foreach ($categoryIds as $categoryId) {
                DB::table('product_category')->insert([
                    'product_id' => $p->id,
                    'category_id' => $categoryId
                ]);
            }
        }
    }



    private function getRandomCategoryIds() {

        // sleep(1);
        // echo "...sleeping in METHOD: getRandomCategoryIds()...";
        $numOfCategories = count(Category::all());
        $numOfIds = rand(1, $numOfCategories);

        $ids = [];

        for ($i=0; $i < $numOfIds; $i++) { 
            $id = rand(1, $numOfCategories);

            if (!in_array($id, $ids)) {
                array_push($ids, $id);
            }
        }

        return $ids;
    }
}
