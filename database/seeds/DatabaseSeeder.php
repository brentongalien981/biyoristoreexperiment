<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            BrandSeeder::class,
            ProductSeeder::class,
            ProductPhotoUrlSeeder::class,
            CategorySeeder::class,
            ProductCategorySeeder::class,
            UserSeeder::class,
            ProfileSeeder::class,
            PaymentInfoSeeder::class,
            AddressSeeder::class,
            CartSeeder::class,
            CartItemSeeder::class,
            StripeCustomerSeeder::class,
        ]);
    }
}
