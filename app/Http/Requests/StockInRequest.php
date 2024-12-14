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
        return [
            '*.lot_no' => 'required|string|max:255',  // Validate each item's lot_no field
            '*.stock_invoice_details_id' => 'nullable|exists:stock_invoice_details,id',
            '*.invoice_id' => 'nullable|exists:stock_invoices,id',
            '*.invoice_no' => 'nullable|string|max:255',
            '*.width' => 'nullable|string|max:255',
            '*.length' => 'nullable|string|max:255',
            '*.unit' => 'nullable|string|max:255',
            '*.qty' => 'nullable|numeric|min:0',
        ];
    }
}
