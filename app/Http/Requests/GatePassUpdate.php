<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GatePassUpdate extends FormRequest
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
            'godown_supervisor_id' => 'required|integer|exists:users,id',
            'date' => 'nullable|date',
            'out_products' => 'required|array',
            'out_products.*.stock_available_id' => 'required|integer|exists:stocks_ins,id',
            'out_products.*.gate_pass_id' => 'nullable|integer|exists:gate_passes,id',
            'out_products.*.product_id' => 'required|integer|exists:products,id',
            'out_products.*.product_type' => 'nullable|string|max:255',
            'out_products.*.hsn_sac_code' => 'nullable|string|max:255',
            'out_products.*.out_width' => 'required|numeric|min:0',
            'out_products.*.out_length' => 'required|numeric|min:0',
            'out_products.*.out_quantity' => 'nullable|numeric|min:0',
            'out_products.*.unit' => 'required|string|max:50',
        ];
           return $rules;
    }
}
