<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerticalStock extends FormRequest
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
        return [
            'product_category_id' => 'required|exists:product_categories,id',
            'product_id' => 'required|exists:products,id',
            'stock_code'=>'required|exists:products,stock_code',
            'lot_no' => 'nullable|string|max:255',
            'length' => 'nullable|numeric|min:0',
            'length_unit' => 'required|string|max:50|in:meter,feet', 
            'rack' => 'nullable|string|max:255',
        ];
        
    }
}
