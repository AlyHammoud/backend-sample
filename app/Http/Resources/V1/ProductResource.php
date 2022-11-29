<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'images' => ImageResource::collection($this->productImages),
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'is_available' => $this->when(
                request()->route()->named(['product.allProducts', 'product.singleproduct']),
                $this->is_available
            ),
            'translations' => $this->when(
                request()->route()->named(['product.allProducts', 'product.singleproduct']),
                $this->translations
            ),
        ];
    }
}
