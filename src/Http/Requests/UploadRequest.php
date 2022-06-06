<?php

namespace Armincms\Iranmobleh\Http\Requests; 

class UploadRequest extends Request
{  
    public function authorize()
    {
        return optional($this->user())->can('update', $this->findResource());
    } 

    public function rules()
    { 
        return [
            'file.*' => 'image'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'file.*' => __('Image')
        ];
    }
}
