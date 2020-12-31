<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'productPhotoUrls' => $this->productPhotoUrls,
            'brand' => $this->brand,
            'categories' => $this->categories,
            'quantity' => $this->quantity,
            'packageItemTypeId' => $this->package_item_type_id,
            'sellers' => SellerResource::collection($this->sellers)
        ];
    }
}
