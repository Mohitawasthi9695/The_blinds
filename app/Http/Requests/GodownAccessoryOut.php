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
            '*.stockout_details_id' => 'required|integer|exists:stock_out_details,id',
            '*.godown_accessory_id' => 'required|integer|exists:godown_accessories,id',
            '*.product_accessory_id' => 'required|integer|exists:product_accessories,id',
            '*.hsn_sac_code' => 'nullable|string|max:255',
            '*.lot_no' => 'nullable|string|max:255',
            '*.date' => 'nullable|date',
            '*.length' => 'nullable|numeric|min:0',
            '*.length_unit' => 'nullable|string|in:cm,mm,m,in,ft',
            '*.quantity' => 'nullable|numeric|min:1',
            '*.rate' => 'nullable|numeric|min:0',
            '*.gst' => 'nullable|numeric|min:0',
            '*.amount' => 'nullable|numeric|min:0',
        ];
        return $rules;
    }
}
