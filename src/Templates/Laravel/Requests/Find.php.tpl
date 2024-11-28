<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Find extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if(!$this->request->has('search'))
            $this->request->add(['search' => $this->route('search')]);
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
            "search" => "string|between:3,100",
        ];
    }
}
