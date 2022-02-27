<?php

namespace Armincms\Iranmobleh\Gutenberg\Templates; 

use Zareismail\Gutenberg\Template; 
use Zareismail\Gutenberg\Variable;
use Armincms\Koomeh\Models\KoomehProperty;

class MyPropertyTableRow extends Template 
{       
    /**
     * The logical group associated with the widget.
     *
     * @var string
     */
    public static $group = 'Users Dashboard';
    
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

            Variable::make('editUrl', __('Property edit URL')),

            Variable::make('images', __(
                "Property gallery image list. available conversions is:[{$conversions}]"
            )),

            Variable::make('hits', __('Property Hits')),

            Variable::make('propertyType.name', __('Property Type Name')),

            Variable::make('propertyType.icon', __('Property Type Icon')),

            Variable::make('propertyType.help', __('Property Type Help')), 

            Variable::make('roomType.name', __('Property Room Type Name')),

            Variable::make('roomType.icon', __('Property Room Type Icon')),

            Variable::make('roomType.help', __('Property Room Type Help')), 

            Variable::make('creation_date', __('Property Creation Date')),

            Variable::make('last_update', __('Property Update Date')), 
        ];
    } 
}
