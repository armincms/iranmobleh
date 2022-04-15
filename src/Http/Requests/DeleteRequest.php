<?php

namespace Armincms\Iranmobleh\Http\Requests; 

class DeleteRequest extends Request
{  
    public function authorize()
    {
        return optional($this->user())->can('delete', $this->findResource());
    } 

    public function rules()
    { 
        return [];
    }
}
