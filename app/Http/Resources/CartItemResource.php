<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
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
            'cartId' => $this->cart_id,
            'productId' => $this->product_id,
            'quantity' => $this->quantity,
            'product' => new ProductResource($this->product)
        ];
    }
}
