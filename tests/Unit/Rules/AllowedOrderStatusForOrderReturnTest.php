<?php

namespace Tests\Unit;

use App\Order;
use Tests\TestCase;
use App\OrderStatus;
use Database\Seeders\OrderStatusSeeder;
use App\Rules\AllowedOrderStatusForOrderReturn;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AllowedOrderStatusForOrderReturnTest extends TestCase
{
    use RefreshDatabase;



    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrderStatusSeeder::class);
    }


    
    /** @test */
    public function it_validates_order_status_for_order_return()
    {
        $o1 = Order::factory()->create(['status_code' => OrderStatus::getCodeByName('DISPATCHED')]);
        $o2 = Order::factory()->create(['status_code' => OrderStatus::getCodeByName('DELIVERED')]);
        $o3 = Order::factory()->create(['status_code' => OrderStatus::getCodeByName('BEING_EVALUATED_FOR_PURCHASE')]);

        $this->assertTrue(AllowedOrderStatusForOrderReturn::bmdValidate(['orderId' => $o1->id]));
        $this->assertTrue(AllowedOrderStatusForOrderReturn::bmdValidate(['orderId' => $o2->id]));
        $this->assertFalse(AllowedOrderStatusForOrderReturn::bmdValidate(['orderId' => $o3->id]));
    }
}
