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

    public function getPropertyAttributes($defaults = [])
    { 
        $resource = $this->findResource();

        $attributes = collect([
            'property_locality_id' => 0,
            'property_type_id' => 0,
            'room_type_id' => 0,
            'city_id' => 0,
            'state_id' => 0,
            'zone_id' => 0,
            'minimum_reservation' => 1,
            'accommodation' => 1,
            'max_accommodation' => 1,
            'max_accommodation_payment' => 1,
            'lat' => null,
            'long' => null,
            'payment_basis_id' => 0,
            'reservation_id' => 0,
            'marked_as' => 'draft',
        ])->merge($defaults)->map(function($value, $key) use ($resource) {
            return $resource ? data_get($resource, $key) : $value;
        }); 

        return with($attributes->merge($this->only($attributes->keys()->all())), function($attributes) {
            collect([
                'name',
                'address',
                'condition',
                'summary',
                'content',
                'condition'
            ])->each(function($attribute) use ($attributes) {
                if ($this->exists($attribute)) {
                    $attributes->put("{$attribute}::".app()->getLocale(), $this->input($attribute));
                }
            }); 
 
            $attributes->put('locale::'.app()->getLocale(), app()->getLocale());
            $attributes->put('auth_id', $this->user()->getKey());

            return $attributes->toArray();
        });
    }

    public function prepareAmenitiesForStorage()
    { 
        $callback = function($amenity, $key) { 
            if (intval($amenity) === 1) {
                return [ $key => [ 'value' => 1 ] ];
            } else if (intval($amenity)) {        
                return [ 
                    $amenity => [ 'value' => 1 ]
                ];  
            } else {
                return [null => null];
            } 
        };
        return collect($this->get('amenities'))->mapWithKeys($callback)->filter()->union(
            $this->get('amenity', [])
        )->filter(); 
    } 

    public function rules()
    {  
        return [
            'name' => 'sometimes|string',
            'property_locality_id' => 'sometimes|numeric', 
            'property_type_id' => 'sometimes|numeric', 
            'room_type_id' => 'sometimes|numeric', 
            'city_id' => 'sometimes|numeric', 
            'state_id' => 'sometimes|numeric', 
            'zone_id' => 'sometimes|numeric', 
            'address' => 'sometimes|string', 
            'minimum_reservation' => 'sometimes|numeric', 
            'accommodation' => 'sometimes|numeric', 
            'max_accommodation' => 'sometimes|numeric', 
            'max_accommodation_payment' => 'sometimes|numeric', 
            'lat' => 'sometimes',
            'long' => 'sometimes',
            'payment_basis_id' => 'sometimes|numeric', 
            'reservation_id' => 'sometimes|numeric', 
            'images' => 'sometimes',
            'images.*' => 'image',
            'pricing' => 'sometimes',
            'pricing.*.amount' => 'numeric', 
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => __('Name'),
            'property_locality_id' => __('Locality'),
            'property_type_id' => __('Property Type'),
            'room_type_id' => __('Room Type'),
            'city_id' => __('City'),
            'state_id' => __('State'),
            'zone_id' => __('Zone'),
            'address' => __('Address'),
            'minimum_reservation' => __('Minimum of reservation'),
            'accommodation' => __('Accommodation'),
            'max_accommodation' => __('Maximum of accommodation'),
            'max_accommodation_payment' => __('Maximum of accommodation payment'),
            'lat' => __('Latitude'),
            'long' => __('Longitude'),
            'payment_basis_id' => __('Payment Basis'),
            'reservation_id' => __('Rservation'),
            'images' => __('Image'),
            'images.*' => __('Images'),
            'pricing' => __('Pricing'),
            'pricing.*.amount' => __('Pricing Amount'),
        ];
    }
}
