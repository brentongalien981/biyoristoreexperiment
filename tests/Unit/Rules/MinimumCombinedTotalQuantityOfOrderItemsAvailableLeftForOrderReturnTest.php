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
use Database\Seeders\OrderReturnItemStatusSeeder;
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
            OrderReturnStatusSeeder::class,
            OrderReturnItemStatusSeeder::class
        ]);
    }



    /** @test */
    public function it_allows_order_return_for_at_least_one_return_item()
    {
        $o1 = Order::factory()->create();
        $oi = OrderItem::factory()->create([
            'order_id' => $o1->id,
            'quantity' => 2
        ]);


        $r1 = OrderReturn::factory()->create(['order_id' => $o1->id]);
        $orderReturnItem = OrderReturnItem::factory()->create([
            'order_return_id' => $r1->id,
            'order_item_id' => $oi->id,
            'seller_product_id' => $oi->product_seller_id,
            'size_availability_id' => $oi->size_availability_id,
            'price' => $oi->price,
            'quantity' => 1,
        ]);


        $this->assertTrue(MinimumCombinedTotalQuantityOfOrderItemsAvailableLeftForOrderReturn::bmdValidate(['orderId' => $o1->id]));
        $this->assertEquals(2, Order::find($o1->id)->orderItems[0]->quantity);
        $this->assertEquals(1, OrderReturn::find($r1->id)->returnItems[0]->quantity);
        $this->assertCount(1, OrderReturn::find($r1->id)->returnItems);
    }



    /** @test */
    public function it_only_allows_order_return_if_the_total_quantity_of_order_items_still_have_at_least_one_quantity_left_to_be_returned()
    {
        $o1 = Order::factory()->create();
        $orderItems1 = OrderItem::factory()->count(5)->create(['order_id' => $o1->id]);
        $orderItemX = $orderItems1[1]; // orderItemToLeaveWithOneLastQtyAvailableForReturn


        $r1 = OrderReturn::factory()->create(['order_id' => $o1->id]);
        $returnItems = $this->tryCreateOrderReturnItems($o1, $r1, true, $orderItemX->id);


        /** BMD-DEBUG: For Visualization. */
        $allORIsForOrderItemWithQty = [];
        $allOrderItems = Order::find($o1->id)->orderItems;
        foreach ($allOrderItems as $oi) {

            $allORIsForOI = OrderReturnItem::where('order_item_id', $oi->id)->get();
            $totalQuantitiesByORI = array_sum($allORIsForOI->pluck('quantity')->toArray());


            $allORIsForOrderItemWithQty[] = [
                'orderItemId' => $oi->id,
                'orderItemQty' => $oi->quantity,
                'orderReturnItemIds' => implode(',', $allORIsForOI->pluck('id')->toArray()),
                'totalReturnedQtyForOrderItem' => $totalQuantitiesByORI
            ];

            if ($oi->id == $orderItemX->id) {
                $this->assertEquals($oi->quantity - 1, $totalQuantitiesByORI);
            } else {
                $this->assertEquals($oi->quantity, $totalQuantitiesByORI);
            }
        }

        dump($allORIsForOrderItemWithQty);


        $this->assertTrue(MinimumCombinedTotalQuantityOfOrderItemsAvailableLeftForOrderReturn::bmdValidate(['orderId' => $o1->id]));
        $this->assertEquals(5, Order::find($o1->id)->orderItems->count());



        $r2 = OrderReturn::factory()->create(['order_id' => $o1->id]);
        $lastAllowedOrderReturnItem = OrderReturnItem::factory()->create([
            'order_return_id' => $r2->id,
            'order_item_id' => $orderItemX->id,
            'seller_product_id' => $orderItemX->product_seller_id,
            'size_availability_id' => $orderItemX->size_availability_id,
            'price' => $orderItemX->price,
            'quantity' => 1,
        ]);

        
        $this->assertFalse(MinimumCombinedTotalQuantityOfOrderItemsAvailableLeftForOrderReturn::bmdValidate(['orderId' => $o1->id]));
    }



    private function tryCreateOrderReturnItems($order, $orderReturn, $shouldMaxOutQuantities = false, $orderItemIdToLeaveOneLastQty = null)
    {
        $randomNumOfReturnItems = $shouldMaxOutQuantities ? count($order->orderItems) : rand(1, count($order->orderItems));
        $returnItems = [];
        $alreadyReferencedOrderIds = [];

        for ($i = 0; $i < $randomNumOfReturnItems; $i++) {

            $maxQtyStillAllowedForThisSpecificOrderReturnItem = null;

            foreach ($order->orderItems as $oi) {

                if (in_array($oi->id, $alreadyReferencedOrderIds)) {
                    continue;
                }

                // all-order-return-items-for-order-item
                $allORIsForOI = OrderReturnItem::where('order_item_id', $oi->id)->get();
                $totalQuantitiesByORI = array_sum($allORIsForOI->pluck('quantity')->toArray());


                if ($totalQuantitiesByORI < $oi->quantity) {

                    $maxQtyStillAllowedForThisSpecificOrderReturnItem = $oi->quantity - $totalQuantitiesByORI;
                    $qty = rand(1, $maxQtyStillAllowedForThisSpecificOrderReturnItem);

                    if ($shouldMaxOutQuantities) {
                        $qty = $oi->quantity;

                        if ($oi->id == $orderItemIdToLeaveOneLastQty) {
                            $qty = $oi->quantity - 1;
                        }
                    }

                    $returnItems[] = OrderReturnItem::factory()->create([
                        'order_return_id' => $orderReturn->id,
                        'order_item_id' => $oi->id,
                        'seller_product_id' => $oi->product_seller_id,
                        'size_availability_id' => $oi->size_availability_id,
                        'price' => $oi->price,
                        'quantity' => $qty,
                    ]);

                    $alreadyReferencedOrderIds[] = $oi->id;

                    break;
                }
            }
        }

        return $returnItems;
    }



    private function datetimeXDaysFromNow($x = 0)
    {
        $timestampInXDays = getdate()[0] + ($x * 60 * 60 * 24);
        $datetimeObj = getdate($timestampInXDays);
        return $datetimeObj['year'] . '-' . $datetimeObj['mon'] . '-' . $datetimeObj['mday'];
    }
}
