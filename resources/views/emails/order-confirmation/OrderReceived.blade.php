@component('mail::message')

<h3>Order Confirmation</h3>


<p>
    Hello,

    We're just letting you know that we've received your order and we'll be processing it soon.
    We'll send you another email once it's shipped.

    Thanks for shopping with us!
</p>



<h3>Order ID: {{ $order->id }}</h3>

<?php $total = $order->charged_subtotal + $order->charged_shipping_fee + $order->charged_tax ?>

@component('mail::table')
| | | |||
|-|-|-|-:|-:|
| |||Sub-total|${{ $order->charged_subtotal }}|
| |||Shipping|${{ $order->charged_shipping_fee }}|
| |||Tax|${{ $order->charged_tax }}|
| |||Total|${{ $total }}|
@endcomponent



@component('mail::table')
| | | |
|-|-:|-:|
@foreach($order->orderItems as $i)
| {{ $i->product->name }} <br> qty: {{ $i->quantity }} ||${{ $i->price * $i->quantity }}|
@endforeach
@endcomponent




@component('mail::table')
| | | |
|-|-|-|
|@component('emails.order-confirmation.ShippingInfo', ['order' => $order ])@endcomponent|||
@endcomponent






@component('mail::table')
| Laravel | Table | Example |
| ------------- |:-------------:| --------:|
| Col 2 is | Centered | $10 |
| Col 3 is | Right-Aligned | $20 |
@endcomponent


@endcomponent