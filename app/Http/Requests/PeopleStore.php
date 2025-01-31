<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class PeopleStore extends FormRequest
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
            'name' => 'required|string|max:200|unique:peoples,name',
            'code' => 'nullable|string|max:10',
            'gst_no' => 'nullable|string|max:100|unique:peoples,gst_no',
            'cin_no' => 'nullable|string|max:100|unique:peoples,cin_no',
            'pan_no' => 'nullable|string|max:10|unique:peoples,pan_no',
            'msme_no' => 'nullable|string|max:100|unique:peoples,msme_no',
            'reg_address' => 'nullable|string|max:255',
            'work_address' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:50',
            'tel_no' => 'nullable|string|max:20',
            'email' => 'nullable|string|max:40|email|unique:peoples,email',
            'owner_mobile' => 'nullable|digits:10',
            'people_type' => 'required|string|in:Supplier,Company,Customer'
        ];
    }
}
