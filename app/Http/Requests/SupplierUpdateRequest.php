<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierUpdateRequest extends FormRequest
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
        $supplierId = $this->route('supplier');

        $rules = [
            'name' => 'required|string|max:200|unique:suppliers,name,' . $supplierId,
            'code' => 'nullable|string|max:10',
            'gst_no' => 'nullable|string|max:100|unique:suppliers,gst_no,' . $supplierId,
            'cin_no' => 'nullable|string|max:100|unique:suppliers,cin_no,' . $supplierId,
            'pan_no' => 'nullable|string|max:10|unique:suppliers,pan_no,' . $supplierId,
            'msme_no' => 'nullable|string|max:100|unique:suppliers,msme_no,' . $supplierId,
            'reg_address' => 'nullable|string|max:255',
            'work_address' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:50',
            'tel_no' => 'nullable|string|max:20',
            'email' => 'nullable|string|max:40|email|unique:suppliers,email,' . $supplierId,
            'owner_mobile' => 'nullable|digits:10',
            // 'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'boolean',
        ];

        return $rules;
    }
}
