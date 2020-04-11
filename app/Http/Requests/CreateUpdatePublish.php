<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateUpdatePublish extends FormRequest
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
        $rules = [
            'message' => 'required',
            'asset_mode'  => ['filled', Rule::in(['url', 'file'])],
            'post_type' => ['required', Rule::in(['photo', 'video', 'link'])],
            'post_mode' => ['required', Rule::in(['now', 'schedule'])],
            'fb_album_id' => 'filled|numeric',
            'comment' => 'filled|nullable|string',
            'reply_message' => 'filled|nullable|string'
        ];

        $rules = array_merge($rules, $this->getFileUploadRules());
        $rules = array_merge($rules, $this->getTimePostRules());
        $rules = array_merge_recursive($rules, $this->getVideoRules());
        $rules = array_merge($rules, $this->getLinkRules());

        return $rules;
    }

    /**
     * Get File upload validation rules
     * 
     * @return array $rule
     */
    public function getFileUploadRules() {
        $rules = [];

        if ($this->input('asset_mode', null) == null )
            return [];

        if ($this->input('asset_mode') === 'url')
            $rules['url'] = ['required','url'];
        else if ($this->input('asset_mode') === 'file'){
            $rules['post_file'] = ['required'];
            if ($this->input('post_type') === 'video')
                $rules['post_file'][] = 'mimetypes:video/avi,video/mpeg,video/quicktime';
            else if ($this->input('post_type') === 'photo')
                $rules['post_file'][] = 'image';
        }

        return $rules;
    }

    /**
     * Get video related validation rules
     * 
     * @return array $rule
     */
    public function getVideoRules()
    {
        $rules = [];

        if ($this->input('post_type') !== "video")
            return $rules;

        $rules['video_title'] = 'required';
        if ($this->input('asset_mode') === 'url')
            $rules['url'] = 'regex:/^https?:\/\/[a-z0-9\-]*\.?[a-z0-9]*\.[a-zA-Z0-9\/\-_\.]*\.mp4[\?]?[a-zA-Z=0-9&_\-\.]*$/i';

        return $rules;
    }

     /**
     * Get time post related validation rules
     * 
     * @return array $rule
     */
    public function getTimePostRules()
    {
        $rules = [];

        if ($this->input('post_mode') === 'schedule') {
            $rules['date'] = 'required';
            $rules['time_hour'] = 'required';
            $rules['time_minute'] = 'required';
        }

        return $rules;
    }

     /**
     * Get link related validation rules
     * 
     * @return array $rule
     */
    public function getLinkRules()
    {
        $rules = [];

        if ($this->input('post_type') === 'link')
            $rules['link'] = 'required';

        return $rules;
    }
}
