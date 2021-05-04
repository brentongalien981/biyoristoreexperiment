Ship to:
{{ $order->first_name . ' ' . $order->last_name }}
{{ $order->street }}
{{ $order->city . ', ' . $order->province  }}
{{ $order->country . ', ' . $order->postal_code  }}