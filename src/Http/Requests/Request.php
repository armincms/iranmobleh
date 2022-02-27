<?php

namespace Armincms\Iranmobleh\Http\Requests;

use Armincms\Koomeh\Models\KoomehProperty;
use Illuminate\Foundation\Http\FormRequest; 

class Request extends FormRequest
{   
    /**
     * Create new model.
     * 
     * @param  array  $attributes 
     * @return              
     */
    public function newModel(array $attributes = [])
    {
        return new KoomehProperty($attributes);
    }

    public function findResource(int $propertyId = null)
    {
        return $this->newModel()->find($propertyId ?? $this->route('property'));
    }
    
    public function authorize()
    {
        return optional($this->user())->can('create', $this->newModel());
    }

    public function getPropertyAttributes()
    {
        $attributes = [
            'property_type_id',
            'room_type_id',
            'city_id',
            'state_id',
            'zone_id',
            'minimum_reservation',
            'accommodation',
            'max_accommodation',
            'max_accommodation_payment',
            'lat',
            'long',
            'payment_basis_id',
            'reservation_id',
        ];

        return tap($this->only($attributes), function(&$attributes) {
            $attributes['name::'.app()->getLocale()] = $this->input('name');
            $attributes['address::'.app()->getLocale()] = $this->input('address');
            $attributes['condition::'.app()->getLocale()] = $this->input('condition');
            $attributes['summary::'.app()->getLocale()] = $this->input('summary');
            $attributes['condition::'.app()->getLocale()] = $this->input('condition');
            $attributes['locale::'.app()->getLocale()] = app()->getLocale();
            $attributes['auth_id'] = $this->user()->getKey();
        });
    }

    public function rules()
    {  
        return [
            'name' => 'required|string',
            'property_type_id' => 'required|numeric', 
            'room_type_id' => 'required|numeric', 
            'city_id' => 'required|numeric', 
            'state_id' => 'required|numeric', 
            'zone_id' => 'required|numeric', 
            'address' => 'required|string', 
            'minimum_reservation' => 'required|numeric', 
            'accommodation' => 'required|numeric', 
            'max_accommodation' => 'required|numeric', 
            'max_accommodation_payment' => 'required|numeric', 
            'lat' => 'required',
            'long' => 'required',
            'payment_basis_id' => 'required|numeric', 
            'reservation_id' => 'required|numeric', 
            'images.*' => 'required|image'
        ];
    }
}
