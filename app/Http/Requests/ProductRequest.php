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
            'name' => 'required|string|max:255|',
            'code' => 'string|max:200|unique:products,code,' . $this->id,
            'shadeNo'=>'string|max:200|unique:products,shadeNo,' . $this->id,
            'purchase_shade_no'=> 'string|max:255|unique:products,purchase_shade_no,' . $this->id,

        ];
        return $rules;
    }
}
