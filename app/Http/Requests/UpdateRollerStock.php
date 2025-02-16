<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRollerStock extends FormRequest
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
            'gate_pass_id' => 'sometimes|required|integer|exists:gate_passes,id',
            'gate_pass_no' => 'sometimes|required|string|max:50',
            'gate_pass_date' => 'sometimes|required|date',
            'date' => 'sometimes|required|date',
            'product_id' => 'sometimes|required|integer|exists:products,id',
            'lot_no' => 'sometimes|required|string|max:50',
            'length' => 'sometimes|required|numeric|min:0.1',
            'out_length' => 'sometimes|nullable|numeric|min:0',
            'length_unit' => 'sometimes|required|string|in:meter,feet',
            'width' => 'sometimes|required|numeric|min:0.1',
            'width_unit' => 'sometimes|required|string|in:meter,feet',
            'rack' => 'required|string|max:50',
            'status' => 'sometimes|required|boolean',
        ];
        
    }
}
