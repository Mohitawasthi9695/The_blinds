<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'ifsc_code' => [
                'required',
                'string',
                'regex:/^[A-Za-z]{4}\d{7}$/',
            ],
            'account_number' => 'required|numeric|unique:banks,account_number,' . $this->id,
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'ifsc_code.required' => 'The IFSC Code field is required.',
            'ifsc_code.string' => 'The IFSC Code must be a string.',
            'ifsc_code.regex' => 'The IFSC Code must be in the format ABCD1234567.',
            'account_number.required' => 'The account number is required.',
            'account_number.numeric' => 'The account number must be a number.',
            'account_number.unique' => 'The account number has already been taken.',
        ];
    }

}
