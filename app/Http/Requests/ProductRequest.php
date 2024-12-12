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
            'name' => 'required|string|max:200|unique:products,name,' . $this->id,
            'shadeNo'=>'string|max:200|unique:products,shadeNo,' . $this->id,
            'purchase_shade_no'=> 'string|max:200|unique:products,purchase_shade_no,' . $this->id,

        ];
        return $rules;
    }
}
