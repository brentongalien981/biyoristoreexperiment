<?php

use App\PackageItemType;
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
            OrderStatusSeeder::class,
            SellerSeeder::class,
            ProductSellerSeeder::class,
            SellerAddressSeeder::class,
            PackageItemTypeSeeder::class,
            TeamSeeder::class,
        ]);
    }
}

