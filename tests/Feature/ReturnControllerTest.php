<?php

namespace Tests\Feature;

use App\Order;
use Tests\TestCase;
use App\OrderStatus;
use Database\Seeders\OrderStatusSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\BmdResponseCodes\OrderBmdResponseCodes;

class ReturnControllerTest extends TestCase
{
    use RefreshDatabase;



    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrderStatusSeeder::class);
    }



    /** @test */
    public function it_returns_order_data_when_order_is_validated_for_order_return()
    {
        $order = Order::factory()->create([
            'status_code' => OrderStatus::getCodeByName('DISPATCHED'),
            'latest_delivery_date' => $this->datetimeXDaysFromNow(-45)
        ]);


        $response = $this->json('post', '/api/returns/create', [
            'orderId' => $order->id
        ]);


        $response
            ->assertStatus(200)
            ->assertJson([
                'isResultOk' => true,
            ]);
    }



    /** @test */
    public function it_returns_non_null_result_code_when_order_is_not_valid_for_return()
    {
        // 'min_datetime' => date('Y-m-d H:i:s T', strtotime($r->epBatchEarliestPickupDatetime)),
        $order = Order::factory()->create();
        $response = $this->json('post', '/api/returns/create', ['orderId' => $order->id]);
        $resultCode = OrderBmdResponseCodes::NOT_ALLOWED_ORDER_STATUS_FOR_ORDER_RETURN;
               
        $response
            ->assertStatus(200)
            ->assertJson([
                'isResultOk' => false,
                'resultCode' => $resultCode
            ]);
            
            

        $order2 = Order::factory()->create([
            'status_code' => OrderStatus::getCodeByName('DISPATCHED'),
            'latest_delivery_date' => $this->datetimeXDaysFromNow(45)
        ]);        
        $response2 = $this->json('post', '/api/returns/create', ['orderId' => $order2->id]);
        $resultCode2 = OrderBmdResponseCodes::INVALID_ORDER_RETURN_DATE_WINDOW;

        $response2
            ->assertStatus(200)
            ->assertJson([
                'isResultOk' => false,
                'resultCode' => $resultCode2
            ]);
    }



    private function datetimeXDaysFromNow($x = 0) 
    {
        $timestampInXDays = getdate()[0] + ($x * 60 * 60 * 24);
        $datetimeObj = getdate($timestampInXDays);
        return $datetimeObj['year'] . '-' . $datetimeObj['mon'] . '-' . $datetimeObj['mday'];
    }
}
