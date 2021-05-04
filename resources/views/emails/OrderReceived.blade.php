@component('mail::message')

<h3>Order Confirmation</h3>


<p>
Hello,

We're just letting you know that we've received your order and we'll be processing it soon.
We'll send you another email once it's shipped.

Thanks for shopping with us!
</p>



@component('mail::panel')
Order ID: {{ $order->id }}

Shipping-Info...
@endcomponent



@component('mail::panel')
Order Amounts...
@endcomponent




@component('mail::panel')
Order Items...
@endcomponent

@component('mail::table')
| Laravel       | Table         | Example  |
| ------------- |:-------------:| --------:|
| Col 2 is      | Centered      | $10      |
| Col 3 is      | Right-Aligned | $20      |
@endcomponent

@endcomponent