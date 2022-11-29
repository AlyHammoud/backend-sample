<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'price' => $this->price,
            'images' => ImageResource::collection($this->itemImages),
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'is_available' => $this->when(
                request()->route()->named(['item.allItems', 'item.singleitem']),
                $this->is_available
            ),
            'translations' => $this->when(
                request()->route()->named(['item.allItems', 'item.singleitem']),
                $this->translations
            ),
        ];
    }
}
