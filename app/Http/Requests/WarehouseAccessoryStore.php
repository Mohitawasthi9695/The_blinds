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
            '*.lot_no' => 'nullable|string|max:255',
            '*.date' => 'nullable|date',
            '*.length' => 'nullable|numeric|min:0',
            '*.length_unit' => 'nullable|string|max:255',
            '*.type' => 'nullable|string|max:255',
            '*.items' => 'nullable|numeric|min:0',
            '*.box_bundle' => 'nullable|numeric|min:0',
            '*.box_bundle_unit' => 'nullable|string|max:255',
            '*.quantity' => 'nullable|string|max:255',
             ];
        
    }
}
