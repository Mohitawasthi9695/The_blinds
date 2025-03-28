<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferStore extends FormRequest
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
            'vehicle_no' => 'required|string|max:255',
            'place_of_supply' => 'required|string|max:255',
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'nullable|string|max:255',
            'type' => 'required|string|in:transfer',
            'date' => 'required|date',
            'out_products' => 'required|array',
            'out_products.*.stock_available_id' => 'required|integer|exists:godown_roller_stocks,id',
            'out_products.*.stock_in_id' => 'required|integer|exists:stocks_ins,id',
            'out_products.*.width' => 'required|numeric|min:1',
            'out_products.*.length' => 'required|numeric|min:1',
            'out_products.*.pcs' => 'required|numeric|min:1',
            'out_products.*.length_unit' => 'required|string|max:50',
            'out_products.*.width_unit' => 'required|string|max:50',
        ];
        return $rules;
    }
}
