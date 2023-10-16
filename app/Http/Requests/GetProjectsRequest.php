<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetProjectsRequest extends FormRequest
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
            "participant_id" => "required_with:participant_type|integer",
            "participant_type" => "required_with:participant_id|string|in:company,user",
            "owner_id" => "required_with:owner_type|integer",
            "owner_type" => "required_with:owner_id|string|in:company,user",
        ];
    }
}
