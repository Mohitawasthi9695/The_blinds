<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockOutRequest extends FormRequest
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
            'invoice_no' => 'required|string|max:255',
            'date' => 'required|date',
            'customer_id' => 'required|integer|exists:customers,id',
            'receiver_id' => 'nullable|integer|exists:receivers,id',
            'place_of_supply' => 'nullable|string|max:255',
            'vehicle_no' => 'nullable|string|max:50',
            'station' => 'nullable|string|max:255',
            'ewaybill' => 'nullable|string|max:255',
            'reverse_charge' => 'nullable|boolean',
            'gr_rr' => 'nullable|string|max:255',
            'transport' => 'nullable|string|max:255',
            'irn' => 'nullable|string|max:255',
            'ack_no' => 'nullable|string|max:255',
            'ack_date' => 'nullable|date',
            'total_amount' => 'required|numeric|min:0',
            'cgst_percentage' => 'nullable|numeric|min:0|max:100',
            'sgst_percentage' => 'nullable|numeric|min:0|max:100',
            'payment_mode' => 'nullable|string|in:cash,card,online,cheque,other',
            'payment_status' => 'nullable|string|in:paid,pending,failed',
            'payment_date' => 'nullable|date',
            'payment_bank' => 'nullable|string|max:255',
            'payment_account_no' => 'nullable|string|max:255',
            'payment_ref_no' => 'nullable|string|max:255',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_remarks' => 'nullable|string|max:1000',
            'qr_code' => 'nullable|string|max:1000',
            'status' => 'nullable|boolean',
            'created_at' => 'nullable|date',
            // Out Product fields (array of products)
            'out_products' => 'required|array',
            'out_products.*.stock_available_id' => 'required|integer|exists:stock_available,id',
            'out_products.*.product_id' => 'required|integer|exists:products,id',
            'out_products.*.product_type' => 'nullable|string|max:255',
            'out_products.*.hsn_sac_code' => 'nullable|string|max:255',
            'out_products.*.out_width' => 'required|numeric|min:0',
            'out_products.*.out_length' => 'required|numeric|min:0',
            'out_products.*.out_quantity' => 'required|numeric|min:0',
            'out_products.*.unit' => 'required|string|max:50',
            'out_products.*.rate' => 'required|numeric|min:0',
            'out_products.*.amount' => 'required|numeric|min:0',
            'out_products.*.status' => 'nullable|boolean',
            'out_products.*.created_at' => 'nullable|date',
        ];
    
    }
}