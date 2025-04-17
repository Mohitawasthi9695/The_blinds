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
            'vehicle_no' => 'required|string|max:255',
            'place_of_supply' => 'required|string|max:255',
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'nullable|string|max:255',
            'type' => 'required|string|in:accessory,accessoryTransfer',
            'date' => 'required|date',
            'out_products' => 'required|array',
            'out_products.*.warehouse_accessory_id' => 'required|integer|exists:warehouse_accessories,id',
            'out_products.*.lot_no' => 'nullable|string|max:255',
            'out_products.*.items' => 'nullable|numeric|min:0',
            'out_products.*.length' => 'nullable|numeric|min:0',
            'out_products.*.length_unit' => 'nullable|string|in:cm,mm,m,in,ft',
            'out_products.*.box_bundle' => 'nullable|numeric|min:1',
            'out_products.*.box_bundle_unit' => 'nullable|string|max:255',
            'out_products.*.quantity' => 'nullable|numeric|min:1',
        ];
           return $rules;      
    }
}
