<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Gate::allows('manageProduct', $this->product)) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name_translation' => "sometimes|required|array|min:1",
            'name_translation.*' => "distinct",
            'description_translation' => "sometimes|required|array|min:1",
            'description_translation.*' => "distinct",
            'price' => 'nullable|numeric|min:0.1',
            'is_available' => 'sometimes|required|boolean|max:1',
            'item_id' => 'sometimes|required|exists:items,id',
            'images' => 'sometimes|nullable|array',
            'images.*' => 'image',
            'deleted_images' => 'sometimes|nullable|array',
            'deleted_images.*' => 'string'
        ];
    }
}
