<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccessoryTransport extends FormRequest
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
            'godown_supervisor_id' => 'required|integer|exists:users,id',
            'vehicle_no' => 'required|string|max:255',
            'place_of_supply' => 'required|string|max:255',
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'nullable|string|max:255',
            'type' => 'required|string|in:transfer,accessoryTransfer',
            'date' => 'required|date',
            'out_products' => 'required|array',
            'out_products.*.stock_available_id' => 'required|integer|exists:godown_accessories,id',
            'out_products.*.warehouse_accessory_id' => 'required|integer|exists:warehouse_accessories,id',
            'out_products.*.length' => 'required|numeric|min:0',
            'out_products.*.length_unit' => 'required|string|in:ft,in,m,cm,N/A',
            'out_products.*.items' => 'required|numeric|min:1',
            'out_products.*.box_bundle' => 'required|numeric|min:1',
            'out_products.*.box_bundle_unit' => 'required|string|max:255',
            'out_products.*.out_quantity' => 'required|numeric|min:1',
            'out_products.*.remark' => 'nullable|string|max:255',
            'out_products.*.rack' => 'nullable|string|max:255',

        ];
    }
}
