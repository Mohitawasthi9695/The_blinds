<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockInRequest extends FormRequest
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
            '*.lot_no' => 'nullable|string|max:255',
            '*.invoice_id' => 'required|exists:stock_invoices,id',
            '*.invoice_no' => 'required|exists:stock_invoices,invoice_no',
            '*.product_category_id' => 'required|exists:product_categories,id',
            '*.product_id' => 'required|exists:products,id',
            '*.width' => 'nullable|numeric|min:0',
            '*.length' => 'nullable|numeric|min:0',
            '*.length_unit' => 'nullable|string|max:255',
            '*.width_unit' => 'nullable|string|max:255',
            '*.rack' => 'nullable|string|max:255',
            '*.pcs' => 'nullable|numeric|min:1',
            '*.quantity' => 'required|numeric|min:1',
        ];

        return $rules;
    }
}
