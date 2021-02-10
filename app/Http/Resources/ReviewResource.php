<?php

namespace App\Http\Resources;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $reviewer = User::find($this->user_id);
        $reviewerName = $reviewer->profile->first_name;
        $reviewerLastName = $reviewer->profile->last_name;
        if (isset($reviewerLastName) && strlen($reviewerLastName) > 0) {
            $reviewerName .= ' ' . substr($reviewerLastName, 0, 1) . '.';
        }
        

        return [
            'id' => $this->id,
            'productId' => $this->product_id,
            'userId' => $this->user_id,
            'reviewerName' => $reviewerName,
            'message' => $this->message,
            'rating' => $this->rating,
            'createdAt' => Carbon::parse($this->created_at)->diffForHumans()
        ];
    }
}
