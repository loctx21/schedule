<?php

namespace App\Http\Requests\Page;

use App\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePage extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $page = $this->route('page');
        $user = $page->users()->find($this->user()->id); 
        return $user ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'def_fb_album_id' => 'filled|numeric',
            'conv_index' => ['required', Rule::in([Page::CONV_INDEX_ENABLED, Page::CONV_INDEX_DISABLED])],
            'schedule_time' => ['filled', 'string'],
            'timezone' => ['filled', 'regex:/^[A|E|I|P][a-z]*\/[a-zA-Z_]+$/i']
        ];
    }
}
