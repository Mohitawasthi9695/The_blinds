<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->id) {
            // Update rules
            $rules = [
                'name' => 'required|string|max:200|unique:customers,name,' . $this->id,
                'code' => 'nullable|string|max:10',
                'gst_no' => 'nullable|string|max:100|unique:customers,gst_no,' . $this->id,
                'cin_no' => 'nullable|string|max:100|unique:customers,cin_no,' . $this->id,
                'pan_no' => 'nullable|string|max:10|unique:customers,pan_no,' . $this->id,
                'msme_no' => 'nullable|string|max:100|unique:customers,msme_no,' . $this->id,
                'reg_address' => 'nullable|string|max:255',
                'work_address' => 'nullable|string|max:255',
                'area' => 'nullable|string|max:50',
                'tel_no' => 'nullable|string|max:20',
                'email' => 'nullable|string|max:40|email|unique:customers,email,' . $this->id,
                'owner_mobile' => 'nullable|digits:10',
                // 'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
                'status' => 'boolean',
            ];
        } else {
            $rules = [
                'name' => 'required|string|max:200|unique:customers,name',
                'code' => 'nullable|string|max:10',
                'gst_no' => 'nullable|string|max:100|unique:customers,gst_no',
                'cin_no' => 'nullable|string|max:100|unique:customers,cin_no',
                'pan_no' => 'nullable|string|max:10|unique:customers,pan_no',
                'msme_no' => 'nullable|string|max:100|unique:customers,msme_no',
                'reg_address' => 'nullable|string|max:255',
                'work_address' => 'nullable|string|max:255',
                'area' => 'nullable|string|max:50',
                'tel_no' => 'nullable|string|max:20',
                'email' => 'nullable|string|max:40|email|unique:customers,email',
                'owner_mobile' => 'nullable|digits:10',
                'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
                'status' => 'boolean',
            ];
        }
        return $rules;
    }
}
