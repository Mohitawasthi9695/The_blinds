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
            // '*.stock_invoice_details_id' => 'nullable|exists:stock_invoice_details,id',
            '*.invoice_id' => 'nullable|exists:stock_invoices,id',
            '*.invoice_no' => 'nullable|exists:stock_invoices,invoice_no',
            '*.product_id' => 'nullable|exists:products,id',
            '*.width' => 'nullable|numeric|min:0',
            '*.length' => 'nullable|numeric|min:0',
            '*.unit' => 'nullable|string|max:255',
            '*.type' => 'nullable|string|max:255',
            '*.qty' => 'nullable|numeric|min:0',
        ];
        
        return $rules;
        
    }
}
