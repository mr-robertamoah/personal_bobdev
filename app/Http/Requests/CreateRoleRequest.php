<?php

namespace App\Http\Requests;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->user();

        return $user->isAdmin() ||
            $user->ownsCompany() ||
            $user->ownsProject() ||
            $user->isAuthorizedFor(name: PermissionEnum::CREATEROLES->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "name" => "required|string",
            "class" => "nullable|string",
            "public" => "required|boolean",
            "description" => "nullable|string",
        ];
    }
}
