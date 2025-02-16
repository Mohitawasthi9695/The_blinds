<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockInUpdate extends FormRequest
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
            'lot_no' => 'sometimes|string|max:255', 
            'stock_invoice_details_id' => 'sometimes|exists:stock_invoice_details,id',
            'invoice_id' => 'sometimes|exists:stock_invoices,id',
            'product_id' => 'sometimes|exists:products,id',
            'product_category_id' => 'sometimes|exists:product_categories,id', 
            'width' => 'sometimes|numeric|min:0',
            'length' => 'sometimes|numeric|min:0',
            'length_unit' => 'sometimes|string|max:255', 
            'width_unit' => 'sometimes|string|max:255', 
            'rack' => 'sometimes|string|max:255', 
            'pcs' => 'sometimes|numeric|min:0', 
            'quantity' => 'sometimes|numeric|min:0',
            'date' => 'sometimes|date', 
        ];
        
        return $rules;
        
    }
}
