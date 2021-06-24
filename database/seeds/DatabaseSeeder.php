<?php

use App\PackageItemType;
use Database\Seeders\RoleSeeder;
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
            ShippingServiceLevelSeeder::class,
            TeamSeeder::class,
            ReviewSeeder::class,
            AuthProviderTypeSeeder::class,
            BmdAuthSeeder::class,
            ExchangeRateSeeder::class,
            RoleSeeder::class,
            ScheduledTaskStatusSeeder::class,
            ScheduledTaskSeeder::class
        ]);
    }
}

