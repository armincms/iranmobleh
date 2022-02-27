<?php

namespace Armincms\Iranmobleh\Http\Requests; 

class UpdateRequest extends Request
{  
    public function authorize()
    {
        return optional($this->user())->can('update', $this->findResource());
    } 
}
