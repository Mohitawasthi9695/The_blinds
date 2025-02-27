<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'product_category_id' => 'required|integer|exists:product_categories,id',
            'shadeNo' => 'required|string|max:255',
            'purchase_shade_no' => 'string|max:255|unique:products,purchase_shade_no,' . $this->id,

        ];
        return $rules;
    }
}
