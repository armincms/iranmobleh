<?php

namespace Armincms\Iranmobleh\Cypress\Widgets;

use Armincms\Contract\Gutenberg\Templates\Pagination; 
use Armincms\Contract\Gutenberg\Widgets\BootstrapsTemplate; 
use Armincms\Contract\Gutenberg\Widgets\ResolvesDisplay;  
use Armincms\Iranmobleh\Cypress\Fragments\PropertyForm;   
use Armincms\Iranmobleh\Gutenberg\Templates\MyPropertyTableRow;   
use Armincms\Iranmobleh\Gutenberg\Templates\PropertyTable;   
use Armincms\Koomeh\Nova\Promotion;   
use Armincms\Koomeh\Nova\Property;   
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;    
use Zareismail\Cypress\Http\Requests\CypressRequest;
use Zareismail\Gutenberg\Gutenberg; 
use Zareismail\Gutenberg\GutenbergWidget; 

class MyProperty extends GutenbergWidget
{     
    use BootstrapsTemplate;    
    use ResolvesDisplay;   

    /**
     * The logical group associated with the widget.
     *
     * @var string
     */
    public static $group = 'Users Dashboard';

    /**
     * Bootstrap the resource for the given request.
     * 
     * @param  \Zareismail\Cypress\Http\Requests\CypressRequest $request 
     * @param  \Zareismail\Cypress\Layout $layout 
     * @return void                  
     */
    public function boot(CypressRequest $request, $layout)
    {   
        if (is_null($request->user())) { 
            $this->renderable(false);

            return;
        }

        parent::boot($request, $layout); 

        $template = $this->bootstrapTemplate($request, $layout, $this->metaValue(Property::uriKey()));
 
        $this->displayResourceUsing(function($attributes) use ($template) {   
            return $template->gutenbergTemplate($attributes)->render();
        }, Property::class);  

        $this->when($this->metaValue('pagination'), function() use ($request, $layout) { 
            $template = $this->bootstrapTemplate($request, $layout, $this->metaValue('pagination'));
     
            $this->displayResourceUsing(function($attributes) use ($template) {   
                return $template->gutenbergTemplate($attributes)->render();
            }, 'pagination'); 
        }, function() { 
            $this->displayResourceUsing(function($attributes) { }, 'pagination'); 
        });
    } 

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public static function fields($request)
    {  
        return [ 
            Select::make(__('Display Pagination By'), 'config->pagination')
                ->options(Gutenberg::cachedTemplates()->forHandler(Pagination::class)->keyBy->getKey()->map->name)  
                ->displayUsingLabels()
                ->required()
                ->rules('required'), 

            Select::make(__('Display Properties By'), 'config->'. Property::uriKey())
                ->options(Gutenberg::cachedTemplates()->forHandler(MyPropertyTableRow::class)->keyBy->getKey()->map->name)
                ->required()
                ->rules('required')
                ->displayUsingLabels(), 
                
            Number::make(__('Display per page'), 'config->per_page')
                ->default(1)
                ->min(1)
                ->required()
                ->rules('required', 'min:1')
                ->help(__('Number of items that should be display on each page.')),  
        ];
    } 

    /**
     * Serialize the widget fro template.
     * 
     * @return array
     */
    public function serializeForDisplay(): array
    {   
        $promotions = Promotion::newModel()->actives()->get()->toArray();
        $eagers = ['propertyType', 'media', 'roomType'];
        $properties = Property::newModel()->authorize()->with($eagers)->paginate($this->metaValue('per_page'));
        $editFramgment = Gutenberg::cachedFragments()->forHandler(PropertyForm::class)->first();
        $callback = function($property) use ($editFramgment, $promotions) {
            $attributes = $property->serializeForIndexWidget($this->getRequest());
            $attributes['editUrl'] = optional($editFramgment)->getUrl($property->getKey());
            $attributes['csrf_token'] = csrf_token();
            $attributes['deleteUrl'] = route('iranmoble.property.delete', $property);
            $attributes['promotions'] = $promotions;

            return $this->displayResource($attributes, Property::class);
        };

        return [
            'items' => $properties->getCollection()->map($callback)->implode(''),

            'pagination' => $this->displayResource($properties->toArray(), 'pagination'),
            'session' => session()->all(),
        ];
    }

    /**
     * Query related tempaltes.
     * 
     * @param  $request [description]
     * @param  $query   [description]
     * @return          [description]
     */
    public static function relatableTemplates($request, $query)
    {
        return $query->handledBy(
            \Armincms\Iranmobleh\Gutenberg\Templates\MyPropertyWidget::class
        );
    }
}
