<?php

namespace Armincms\Koomeh\Gutenberg\Templates; 
 
use Armincms\Koomeh\Models\KoomehProperty;
use Zareismail\Gutenberg\Template; 
use Zareismail\Gutenberg\Variable;

class SingleProperty extends Template 
{    
    /**
     * The logical group associated with the widget.
     *
     * @var string
     */
    public static $group = 'Property';

    /**
     * Register the given variables.
     * 
     * @return array
     */
    public static function variables(): array
    {
        $conversions = (new KoomehProperty)->conversions()->implode(',');

        return [
            Variable::make('id', __('Property Id')),

            Variable::make('name', __('Property Name')),

            Variable::make('code', __('Property Code')),

            Variable::make('url', __('Property URL')),

            Variable::make('accommodation', __('Actual property accommodation space')),

            Variable::make('max_accommodation', __('Extra property accommodation space')),
            
            Variable::make('max_accommodation_payment', __('Additional cost per guest by percent')), 

            Variable::make('images', __(
                "Property gallery image list. available conversions is:[{$conversions}]"
            )),

            Variable::make('hits', __('Property Hits')),

            Variable::make('lat', __('Property Google Latitude')),

            Variable::make('long', __('Property Google Longitude')), 

            Variable::make('address', __('Property full address')), 

            Variable::make('propertyLocality.name', __('Property Locality Name')),

            Variable::make('propertyLocality.icon', __('Property Locality Icon')),

            Variable::make('propertyLocality.help', __('Property Locality Help')),

            Variable::make('propertyType.name', __('Property Type Name')),

            Variable::make('propertyType.icon', __('Property Type Icon')),

            Variable::make('propertyType.help', __('Property Type Help')),

            Variable::make('roomType.name', __('Room Type Name')),

            Variable::make('roomType.help', __('Room Type Help')),

            Variable::make('paymentBasis.name', __('Property Payment Basis')), 

            Variable::make('reservation.name', __('Property Reservation Name')), 

            Variable::make('reservation.help', __('Property Reservation Help')), 

            Variable::make('stateName', __('Property State Name')), 

            Variable::make('cityName', __('Property City Name')), 

            Variable::make('zoneName', __('Property Zone Name')),  

            Variable::make('availableDetails', __(
                'Property available amenities list. each amenity has a name, help, value, group and icon.'
            )), 

            Variable::make('countableDetails', __(
                'Property countable amenities list. each amenity has a name, help, value, group and icon.'
            )), 

            Variable::make('descriptiveDetails', __(
                'Property descriptive amenities list. each amenity has a name, help, value, group and icon.'
            )), 

            Variable::make('conditions', __(
                'Property conditions list. each condition has a name, help and groupName.'
            )), 

            Variable::make('conditions', __('List of property conditions')), 

            Variable::make('condition', __('Property custom stay conditions')), 

            Variable::make('creation_date', __('Property Creation Date')),

            Variable::make('last_update', __('Property Update Date')), 

            Variable::make('summary', __('Property Summary')),

            Variable::make('content', __('Property Content')),  
        ];
    } 
}
