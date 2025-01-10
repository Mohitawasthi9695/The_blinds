<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GodownStore extends FormRequest
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
            'warehouse_supervisor_id' => 'nullable|integer|exists:users,id',
            'godown_supervisor_id' => 'nullable|integer|exists:users,id',
            'date' => 'nullable|date',
            'send_products' => 'required|array',
            'send_products.*.stock_available_id' => 'required|integer|exists:stocks_ins,id',
            'send_products.*.product_id' => 'required|integer|exists:products,id',
            'send_products.*.product_type' => 'nullable|string|max:255',
            'send_products.*.stock_code' => 'nullable|string|max:255',
            'send_products.*.hsn_sac_code' => 'nullable|string|max:255',
            'send_products.*.get_width' => 'required|numeric|min:0',
            'send_products.*.get_length' => 'required|numeric|min:0',
            'send_products.*.get_quantity' => 'nullable|numeric|min:0',
            'send_products.*.unit' => 'required|string|max:50',
            'send_products.*.status' => 'nullable|numeric',
        ];
           return $rules;
    }
}
