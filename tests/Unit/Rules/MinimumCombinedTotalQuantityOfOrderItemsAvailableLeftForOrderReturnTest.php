<?php

namespace Tests\Feature;

use App\Order;
use App\OrderItem;
use Tests\TestCase;
use App\OrderReturn;
use App\OrderReturnItem;
use Database\Seeders\TeamSeeder;
use Database\Seeders\BrandSeeder;
use Database\Seeders\SellerSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\OrderStatusSeeder;
use Database\Seeders\ProductSellerSeeder;
use Database\Seeders\PackageItemTypeSeeder;
use Database\Seeders\SizeAvailabilitySeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\OrderReturnStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Rules\MinimumCombinedTotalQuantityOfOrderItemsAvailableLeftForOrderReturn;

class MinimumCombinedTotalQuantityOfOrderItemsAvailableLeftForOrderReturnTest extends TestCase
{
    use RefreshDatabase;



    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            OrderStatusSeeder::class,
            BrandSeeder::class,
            TeamSeeder::class,
            ProductSeeder::class,
            SellerSeeder::class,
            PackageItemTypeSeeder::class,
            ProductSellerSeeder::class,
            SizeAvailabilitySeeder::class,
            OrderReturnStatusSeeder::class
        ]);
    }



    /** @test */
    public function it_only_allows_order_return_if_the_total_quantity_of_order_items_still_have_at_least_one_quantity_left_to_be_returned()
    {
        $o1 = Order::factory()->create();
        $orderItems1 = OrderItem::factory()->count(10)->create(['order_id' => $o1->id]);       
        $this->assertTrue(MinimumCombinedTotalQuantityOfOrderItemsAvailableLeftForOrderReturn::bmdValidate(['orderId' => $o1->id]));


        $r1 = OrderReturn::factory()->create([
            'order_id' => $o1->id            
        ]);
        
        $returnItems1 = OrderReturnItem::factory()->count(rand(1, count($orderItems1)))->create();
        dd($returnItems1);

        $this->assertTrue(MinimumCombinedTotalQuantityOfOrderItemsAvailableLeftForOrderReturn::bmdValidate(['orderId' => $o1->id]));
    }



    private function datetimeXDaysFromNow($x = 0) 
    {
        $timestampInXDays = getdate()[0] + ($x * 60 * 60 * 24);
        $datetimeObj = getdate($timestampInXDays);
        return $datetimeObj['year'] . '-' . $datetimeObj['mon'] . '-' . $datetimeObj['mday'];
    }
}
