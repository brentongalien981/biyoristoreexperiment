<?php

namespace App\Http\Resources;

use App\Http\BmdCacheObjects\OrderStatusCacheObject;
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

            'createdAt' => Carbon::parse($this->created_at)->diffForHumans() 
        ];
    }
}