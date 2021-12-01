<?php

namespace Tests\Unit\Rules;

use App\Order;
use Tests\TestCase;
use App\Rules\ValidOrderReturnDateWindow;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ValidOrderReturnDateWindowTest extends TestCase
{
    use RefreshDatabase;



    /** @test */
    public function it_returns_false_when_order_has_invalid_return_date_window()
    {
        $order = Order::factory()->create([
            'latest_delivery_date' => $this->datetimeXDaysFromNow(-46)
        ]);        


        $isValid = ValidOrderReturnDateWindow::bmdValidate([
            'orderId' => $order->id
        ]);

        // dd($numOfDaysFromDeliveryToNow);

        
        $this->assertFalse($isValid);
        
    }



    /** @test */
    public function it_returns_true_when_order_has_valid_return_date_window()
    {
        $order = Order::factory()->create(['latest_delivery_date' => $this->datetimeXDaysFromNow(-46)]);        
        $order2 = Order::factory()->create(['latest_delivery_date' => $this->datetimeXDaysFromNow(-45)]);
        $order3 = Order::factory()->create(['latest_delivery_date' => $this->datetimeXDaysFromNow(-44)]);        
        $order4 = Order::factory()->create(['latest_delivery_date' => $this->datetimeXDaysFromNow(0)]);  
        $order5 = Order::factory()->create(['latest_delivery_date' => $this->datetimeXDaysFromNow(1)]);  
        $order6 = Order::factory()->create(['latest_delivery_date' => $this->datetimeXDaysFromNow(2)]);  


        $this->assertFalse(ValidOrderReturnDateWindow::bmdValidate(['orderId' => $order->id]));
        $this->assertTrue(ValidOrderReturnDateWindow::bmdValidate(['orderId' => $order2->id]));
        $this->assertTrue(ValidOrderReturnDateWindow::bmdValidate(['orderId' => $order3->id]));
        $this->assertFalse(ValidOrderReturnDateWindow::bmdValidate(['orderId' => $order4->id]));
        $this->assertFalse(ValidOrderReturnDateWindow::bmdValidate(['orderId' => $order5->id]));
        $this->assertFalse(ValidOrderReturnDateWindow::bmdValidate(['orderId' => $order6->id]));

        $this->assertCount(6, Order::all());

    }    



    private function datetimeXDaysFromNow($x = 0) 
    {
        $timestampInXDays = getdate()[0] + ($x * 60 * 60 * 24);
        $datetimeObj = getdate($timestampInXDays);
        return $datetimeObj['year'] . '-' . $datetimeObj['mon'] . '-' . $datetimeObj['mday'];
    }
}
