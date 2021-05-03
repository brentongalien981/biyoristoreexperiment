@component('mail::message')
<h3>Thank You! We've Received Your Order</h3>

<h4>Order Details:</h4>
<h6>order-id: {{ $order->id }}</h6>
<h6>order-email: {{ $order->email }}</h6>



Thanks,<br>
{{ config('app.name') }}
@endcomponent