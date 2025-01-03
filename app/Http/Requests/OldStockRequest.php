<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OldStockRequest extends FormRequest
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
            '*.product_id' => 'nullable|exists:products,id',
            '*.width' => 'nullable|numeric|min:0',
            '*.length' => 'nullable|numeric|min:0',
            '*.unit' => 'nullable|string|max:255',
            '*.type' => 'nullable|string|max:255',
            '*.rack' => 'nullable|numeric|min:0',
        ];
        
        return $rules;
        
    }
}
