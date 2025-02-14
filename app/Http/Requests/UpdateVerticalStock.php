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
            'get_length' => 'required|numeric|min:0'
        ];
    }
}
