<?php

namespace App\Http\Resources;

use App\Http\BmdCacheObjects\OrderStatusCacheObject;
use App\MyConstants\BmdGlobalConstants;
use App\Order;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'stripePaymentIntentId' => $this->stripe_payment_intent_id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'street' => $this->street,
            'city' => $this->city,
            'province' => $this->province,
            'country' => $this->country,
            'postalCode' => $this->postal_code,
            'phone' => $this->phone,
            'email' => $this->email,

            // 'statusId' => $this->status_id,
            'status' => OrderStatusCacheObject::getDataByCode($this->status_code),
            'orderItems' => OrderItemResource::collection($this->orderItems),

            'chargedSubtotal' => $this->charged_subtotal,
            'chargedShippingFee' => $this->charged_shipping_fee,
            'chargedTax' => $this->charged_tax,

            'earliestDeliveryDays' => $this->projected_total_delivery_days - BmdGlobalConstants::PAYMENT_TO_FUNDS_PERIOD - BmdGlobalConstants::ORDER_PROCESSING_PERIOD,
            'latestDeliveryDays' => $this->projected_total_delivery_days,
            // 'earliestDeliveryDate' => Order::getReadableDate($this->earliest_delivery_date),
            'latestDeliveryDate' => Order::getReadableDate($this->latest_delivery_date),

            'createdAt' => Carbon::parse($this->created_at)->diffForHumans() 
        ];
    }
}