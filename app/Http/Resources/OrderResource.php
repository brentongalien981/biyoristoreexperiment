<?php

namespace App\Http\Resources;

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

            'street' => $this->street,
            'city' => $this->city,
            'province' => $this->province,
            'country' => $this->country,
            'postalCode' => $this->postal_code,
            'phone' => $this->phone,
            'email' => $this->email,

            'statusId' => $this->status_id,
            'status' => $this->status,
            'orderItems' => OrderItemResource::collection($this->orderItems),
        ];
    }
}