<?php

namespace App\Http\Requests\Publish;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Publish\TraitRule;

class CreatePublish extends FormRequest
{
    use TraitRule;

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
        $rules = $this->getPostRule();
        $rules = array_merge($rules, $this->getFileUploadRules());
        $rules = array_merge($rules, $this->getTimePostRules());
        $rules = array_merge_recursive($rules, $this->getVideoRules());
        $rules = array_merge($rules, $this->getLinkRules());

        return $rules;
    }

}
