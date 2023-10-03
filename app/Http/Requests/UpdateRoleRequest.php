<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
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
            "name" => "required_without_all:class,public,description|string",
            "class" => "required_without_all:name,public,description|string",
            "public" => "required_without_all:class,name,description|boolean",
            "description" => "required_without_all:class,public,name|string",
        ];
    }
}
