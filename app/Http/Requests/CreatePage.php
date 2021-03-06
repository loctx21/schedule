<?php

namespace App\Http\Requests;

use App\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePage extends FormRequest
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
            'name'  => 'required',
            'fb_id' => 'required|numeric',
            'def_fb_album_id' => 'filled|numeric',
            'conv_index' => ['filled', Rule::in([Page::CONV_INDEX_ENABLED, Page::CONV_INDEX_DISABLED])]
        ];
    }
}
