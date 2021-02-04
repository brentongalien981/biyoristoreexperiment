<?php

namespace App\Http\Resources;

use App\SellerProduct;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $sellerProduct = SellerProduct::find($this->pivot->id);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'alternateName' => $this->alternate_name,
            'address' => $this->sellerAddress,
            'productSeller' => $this->pivot,
            'sizeAvailabilities' => $sellerProduct->sizeAvailabilities ?? []
        ];
    }
}
