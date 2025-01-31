<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseAccessoryStore extends FormRequest
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
            '*.product_accessory_id' => 'required|exists:product_accessories,id',
            '*.length' => 'nullable|numeric|min:0',
            '*.unit' => 'nullable|string|max:255',
            '*.items' => 'nullable|numeric|min:0',
            '*.box' => 'nullable|numeric|min:0',
            '*.quantity' => 'nullable|numeric|min:0',
        ];
        
    }
}
