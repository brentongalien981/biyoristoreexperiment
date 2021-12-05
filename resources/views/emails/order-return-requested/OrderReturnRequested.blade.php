@component('mail::message')

<h1>Order Return Requested</h1>
<br>

<div>
<p>
Here's the details of the order being returned:

Order ID: {{ $order->id }}
<br>
Order Email: {{ $order->email }}
<br>
First Name: {{ $order->first_name }}
<br>
Last Name: {{ $order->last_name }}

Proceed by emailing the customer about the order-return request.
Good luck!
</p>
</div>

@endcomponent