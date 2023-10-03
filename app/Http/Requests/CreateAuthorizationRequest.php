<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAuthorizationRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "authorizable_type" => "required|string",
            "authorizable_id" => "required|string|integer",
            "authorization_type" => "required|string",
            "authorization_id" => "required|string|integer",
            "authorized_id" => "required|string|integer",
        ];
    }
}
