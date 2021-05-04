<?php

namespace App\Mail;

use App\MyConstants\BmdGlobalConstants;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $subject = 'Thank You - We\'ve Received Your Order';
    public $extraData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
        $this->extraData = [
            'latestEstimatedArrival' => 'May 15, 2021'
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // BMD-ON-STAGING: bcc the appropriate @bmd.com email.
        return $this->from(BmdGlobalConstants::EMAIL_SENDER_FOR_ORDER_RECEIVED)
            ->subject($this->subject)
            ->markdown('emails.order-confirmation.OrderReceived');
    }
}
