<?php

namespace App\Http\Resources;

use App\Http\BmdCacheObjects\SizeAvailabilityModelCacheObject;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'productId' => $this->product_id,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'sizeAvailability' => SizeAvailabilityModelCacheObject::getUpdatedModelCacheObjWithId($this->size_availability_id)->data ?? [],
            'product' => new ProductResource($this->product)
        ];
    }
}
