<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StockInvoiceRequest extends FormRequest
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
            'invoice_no' => 'required|string||max:255|unique:stock_invoices,invoice_no',
            'supplier_id' => 'exists:suppliers,id',
            'bank_id' => 'exists:banks,id',
            'date' => 'date',
            'place_of_supply' => 'nullable|string|max:255',
            'vehicle_no' => 'nullable|string|max:255',
            'station' => 'nullable|string|max:255',
            'ewaybill' => 'nullable|string|max:255',
            'gr_rr' => 'nullable|string|max:255',
            'transport' => 'nullable|string|max:255',
            'agent' => 'nullable|string|max:255',
            'warehouse' => 'nullable|string|max:255',   
            'irn' => 'nullable|string|max:255',
            'ack_no' => 'nullable|string|max:255',
            'ack_date' => 'nullable|date',
            'total_amount' => 'numeric|min:0|max:9999999999999.99',
            'cgst_percentage' => 'nullable|numeric|min:0|max:100',
            'sgst_percentage' => 'nullable|numeric|min:0|max:100',
            'qr_code' => 'nullable|string|max:255',
            'products' => 'array|min:1',
            'products.*.total_product' => 'numeric|min:1',
            'products.*.product_type' => 'nullable|string|max:255',
            'products.*.product_id' => 'exists:products,id',
            'products.*.hsn_sac_code' => 'nullable|string|max:255',
            'products.*.quantity' => 'numeric|min:0',
            'products.*.unit' => 'nullable|string|max:50',
            'products.*.width' => 'numeric|min:0',
            'products.*.rate' => 'numeric|min:0',
            'products.*.amount' => 'numeric|min:0',
        ];
        return $rules;
    }
}
