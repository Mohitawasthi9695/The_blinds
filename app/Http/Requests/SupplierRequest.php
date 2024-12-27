<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
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
        $id = $this->route('supplier');

        $rules = [
            'name' => [
                'required',
                'string',
                'max:200',
                Rule::unique('suppliers', 'name')->ignore($id),
            ],
            'code' => 'nullable|string|max:10',
            'gst_no' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('suppliers', 'gst_no')->ignore($id),
            ],
            'cin_no' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('suppliers', 'cin_no')->ignore($id),
            ],
            'pan_no' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('suppliers', 'pan_no')->ignore($id),
            ],
            'msme_no' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('suppliers', 'msme_no')->ignore($id),
            ],
            'reg_address' => 'nullable|string|max:255',
            'work_address' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:50',
            'tel_no' => 'nullable|string|max:20',
            'email' => [
                'nullable',
                'string',
                'max:40',
                'email',
                Rule::unique('suppliers', 'email')->ignore($id),
            ],
            'owner_mobile' => 'nullable|digits:10',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'boolean',
        ];

        return $rules;
    }
}
