<?php

namespace App\Http\Requests;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->user();
        
        return $user->isAdmin() || $user->isAuthorizedFor(name: PermissionEnum::CREATEPERMISSIONS->value);
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
