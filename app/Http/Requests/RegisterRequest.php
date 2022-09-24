<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'firstName' => 'required|string',
            'surname' => 'required|string',
            'otherNames' => 'nullable|string',
            'username' => 'required|min:8|alpha_dash|unique:users,username',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'gender' => 'nullable|in:male,female'
        ];
    }
}
