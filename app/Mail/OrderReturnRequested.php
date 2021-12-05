<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\MyConstants\BmdGlobalConstants;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderReturnRequested extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $subject;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
        $this->subject = 'Order Return Requested - Order ID: ' . $order->id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(BmdGlobalConstants::EMAIL_SENDER_FOR_GENERAL_PURPOSES)
            ->subject($this->subject)
            ->markdown('emails.order-return-requested.OrderReturnRequested');
    }
}
