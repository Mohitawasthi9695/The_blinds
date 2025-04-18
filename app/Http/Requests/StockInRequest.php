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
            '*.lot_no' => 'required|string|max:255',
            '*.invoice_id' => 'required|exists:stock_invoices,id',
            '*.product_category_id' => 'required|exists:product_categories,id',
            '*.product_id' => 'required|exists:products,id',
            '*.width' => 'required|numeric|min:0',
            '*.date' => 'required|date',
            '*.length' => 'required|numeric|min:0',
            '*.length_unit' => 'required|string|max:255|in:cm,m,ft,in,mm',
            '*.width_unit' => 'required|string|max:255|in:cm,m,ft,in,mm',
            '*.rack' => 'nullable|string|max:255',
            '*.remark' => 'nullable|string|max:255',
            '*.pcs' => 'required|numeric|min:1',
            '*.quantity' => 'required|numeric|min:1',
        ];

        return $rules;
    }
}
