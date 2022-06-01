<?php

namespace Armincms\Iranmobleh\Http\Requests; 

class PromotionRequest extends Request
{  
    public function authorize()
    {
        return optional($this->user())->can('update', $this->findResource());
    } 

    public function rules()
    { 
        return [
            'promotion' => 'required|numeric'
        ];
    }

    public function getPropertyAttributes()
    {
        return [
            'promotion' => __('Promotion')
        ];
    }
}
