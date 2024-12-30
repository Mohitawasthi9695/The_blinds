<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierUpdate extends FormRequest
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

        return [
            'name' => "sometimes|required|string|max:200|unique:suppliers,name,{$id}",
            'code' => 'sometimes|nullable|string|max:10',
            'gst_no' => "sometimes|nullable|string|max:100|unique:suppliers,gst_no,{$id}",
            'cin_no' => "sometimes|nullable|string|max:100|unique:suppliers,cin_no,{$id}",
            'pan_no' => "sometimes|nullable|string|max:10|unique:suppliers,pan_no,{$id}",
            'msme_no' => "sometimes|nullable|string|max:100|unique:suppliers,msme_no,{$id}",
            'reg_address' => 'sometimes|nullable|string|max:255',
            'work_address' => 'sometimes|nullable|string|max:255',
            'area' => 'sometimes|nullable|string|max:50',
            'tel_no' => 'sometimes|nullable|string|max:20',
            'email' => "sometimes|nullable|string|max:40|email|unique:suppliers,email,{$id}",
            'owner_mobile' => 'sometimes|nullable|digits:10',
            // 'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'sometimes|boolean',
        ];
    }
}
