<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CutAccessoryStore extends FormRequest
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
            '*.godown_accessory_id' => 'required|integer|exists:godown_accessories,id',
           '*.date' => 'required|date',
            '*.lot_no' => 'required|string|max:255',
            '*.type' => 'required|string|in:entry',
            '*.length' => 'nullable|numeric|min:1',
            '*.length_unit' => 'nullable|string|in:cm,mm,m,in,ft',
            '*.quantity' => 'required|numeric|min:1',
            '*.remark' => 'nullable|string|max:255',
            '*.rack' => 'nullable|string|max:255',
            '*.status' => 'required|numeric|in:1,0',
        ];
    }
}
