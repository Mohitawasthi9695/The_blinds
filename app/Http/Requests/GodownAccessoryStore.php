<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GodownAccessoryStore extends FormRequest
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
            'invoice_no' => 'required|string|max:255',
            'godown_supervisor_id' => 'required|integer|exists:users,id',
            'date' => 'nullable|date',
            'out_products' => 'required|array',
            'out_products.*.warehouse_accessory_id' => 'required|integer|exists:warehouse_accessories,id',
            // 'out_products.*.product_accessory_id' => 'required|integer|exists:product_accessories,id',
            'out_products.*.lot_no' => 'nullable|string|max:255',
            'out_products.*.items' => 'required|numeric|min:0',
            'out_products.*.out_length' => 'required|numeric|min:0',
            'out_products.*.box' => 'required|numeric|min:0',
            'out_products.*.out_quantity' => 'nullable|numeric|min:0',
            'out_products.*.unit' => 'required|string|max:50',
        ];
           return $rules;      
    }
}
