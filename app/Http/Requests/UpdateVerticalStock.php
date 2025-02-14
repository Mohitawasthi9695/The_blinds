<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVerticalStock extends FormRequest
{
    public function authorize()
    {
        return true; // Change this if you need authorization logic
    }

    public function rules()
    {
        return [
            'gate_pass_id' => 'required|exists:gate_passes,id',
            'product_id' => 'required|exists:products,id',
            'stock_code' => 'required|string|max:255|unique:godown_vertical_stocks,stock_code,' . $this->route('godownVerticalStock'),
            'lot_no' => 'nullable|string|max:255',
            'length' => 'required|numeric|min:0',
            'out_length' => 'nullable|numeric|min:0',
            'get_length' => 'nullable|numeric|min:0',
            'length_unit' => 'required|string|max:50',
            'rack' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ];
    }
}
