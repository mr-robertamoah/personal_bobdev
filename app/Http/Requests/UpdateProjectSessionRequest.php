<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectSessionRequest extends FormRequest
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
            "name" => "required_without_all:day_of_week,description,start_date,end_date,type,period,start_time,end_time|string",
            "description" => "required_without_all:day_of_week,name,start_date,end_date,type,period,start_time,end_time|string",
            "day_of_week" => "required_without_all:description,name,start_date,end_date,type,period,start_time,end_time|integer",
            "start_date" => "required_without_all:day_of_week,description,name,,end_date,type,period,start_time,end_time|string",
            "end_date" => "required_without_all:day_of_week,description,name,start_date,type,period,start_time,end_time|string",
            "start_time" => "required_without_all:day_of_week,description,name,start_date,end_date,type,period,end_time|string",
            "end_time" => "required_without_all:day_of_week,description,name,start_date,end_date,type,period,start_time|string",
            "type" => "required_without_all:day_of_week,description,name,start_date,end_date,period,start_time,end_time|string",
            "period" => "required_without_all:day_of_week,description,name,start_date,end_date,type,start_time,end_time|string",
        ];
    }
}
