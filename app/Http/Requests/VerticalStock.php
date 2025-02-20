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
            '*.gate_pass_id' => 'required|integer|exists:gate_passes,id',
            '*.stock_in_id' => 'required|integer|exists:stocks_ins,id',
            '*.gate_pass_no' => 'required|string|max:50',
            '*.gate_pass_date' => 'required|date',
            '*.date' => 'required|date',
            '*.product_id' => 'required|integer|exists:products,id',
            '*.lot_no' => 'required|string|max:50',
            '*.length' => 'required|numeric|min:0',
            '*.length_unit' => 'required|string|in:meter,feet',
            '*.type' => 'required|string|max:50|in:stock,data',
            '*.rack' => 'required|string|max:50',
        ];
    }
}
