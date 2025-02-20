<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GodownAccessoryOut extends FormRequest
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
            '*.stockout_inovice_id' => 'required|integer|exists:stockout_inovices,id',
            '*.godown_accessory_id' => 'required|integer|exists:godown_accessories,id',
            '*.product_accessory_id' => 'required|integer|exists:product_accessories,id',
            '*.lot_no' => 'nullable|string|max:255',
            '*.date' => 'nullable|date',
            '*.items' => 'nullable|numeric|min:0',
            '*.out_length' => 'nullable|numeric|min:0',
            '*.box_bundle' => 'nullable|numeric|min:0',
            '*.out_quantity' => 'nullable|numeric|min:1',
            '*.length_unit' => 'nullable|string|max:50',
        ];
        return $rules;
    }
}
