<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name_translation' => "required|array|min:1",
            'name_translation.*' => "distinct",
            'description_translation' => "required|array|min:1",
            'description_translation.*' => "distinct",
            'price' => 'nullable|numeric|min:0',
            'is_available' => 'required|boolean|max:1',
            'category_id' => 'required|exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image'
        ];
    }
}
