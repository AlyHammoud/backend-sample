<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class CategoryResource extends JsonResource
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
            'is_available' => $this->is_available,
            "created_at" => $this->created_at->diffForHumans(),
            "name" => $this->name,
            "slug" => $this->slug,
            "image_url" => $this->image_url ? URL::to("images/" . $this->image_url) : null,
            // "translations" => $this->translations,
            "translations" =>
            $this->when(
                request()->route()->named(['category.allCategories', 'category.singleCategory']),
                $this->translations
            ),
        ];
    }
}
