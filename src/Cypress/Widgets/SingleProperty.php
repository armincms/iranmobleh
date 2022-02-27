<?php

namespace Armincms\Koomeh\Cypress\Widgets;
  
use Laravel\Nova\Fields\Select;
use Zareismail\Cypress\Widget;  
use Zareismail\Cypress\Http\Requests\CypressRequest;
use Zareismail\Gutenberg\GutenbergWidget; 

class SingleProperty extends GutenbergWidget
{        
    /**
     * Indicates if the widget should be shown on the component page.
     *
     * @var \Closure|bool
     */
    public $showOnComponent = false;

    /**
     * The logical group associated with the widget.
     *
     * @var string
     */
    public static $group = 'Property';

    /**
     * Bootstrap the resource for the given request.
     * 
     * @param  \Zareismail\Cypress\Http\Requests\CypressRequest $request 
     * @param  \Zareismail\Cypress\Layout $layout 
     * @return void                  
     */
    public function boot(CypressRequest $request, $layout)
    {   
        parent::boot($request, $layout);

        $this->withMeta([
            'resource' => $request->resolveFragment()->metaValue('resource')
        ]);
    } 

    /**
     * Serialize the widget fro template.
     * 
     * @return array
     */
    public function serializeForDisplay(): array
    { 
        return $this->metaValue('resource')->serializeForWidget($this->getRequest());
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
            \Armincms\Koomeh\Gutenberg\Templates\SingleProperty::class
        );
    }
}
