<?php

namespace Armincms\Iranmobleh\Cypress\Widgets;
 
use Armincms\Contract\Gutenberg\Widgets\BootstrapsTemplate; 
use Armincms\Contract\Gutenberg\Widgets\ResolvesDisplay;  
use Armincms\Iranmobleh\Cypress\Fragments\PropertyForm;   
use Armincms\Iranmobleh\Gutenberg\Templates\MyPropertyTableRow;   
use Armincms\Iranmobleh\Gutenberg\Templates\PropertyTable;   
use Armincms\Koomeh\Nova\Amenity;   
use Armincms\Koomeh\Nova\Condition;   
use Armincms\Koomeh\Nova\PaymentBasis;   
use Armincms\Koomeh\Nova\Pricing;   
use Armincms\Koomeh\Nova\Property;   
use Armincms\Koomeh\Nova\PropertyType;   
use Armincms\Koomeh\Nova\Reservation;   
use Armincms\Koomeh\Nova\RoomType;   
use Armincms\Location\Nova\State;   
use Armincms\Location\Nova\City;   
use Armincms\Location\Nova\Zone;   
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;    
use Zareismail\Cypress\Http\Requests\CypressRequest;
use Zareismail\Gutenberg\Gutenberg; 
use Zareismail\Gutenberg\GutenbergWidget; 
use Zareismail\Gutenberg\Templates\Blank; 

class EditMyProperty extends GutenbergWidget
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

        if (! $this->profileFilled($request)) { 

            $template = $this->bootstrapTemplate(
                $request, 
                $layout, 
                $this->metaValue('profile.error_view')
            );

            $this->displayUsing(function($attributes) use ($template) { 
                return $template->gutenbergTemplate($attributes)->render(); 
            });
        }

        $this->withMeta([
            'errors' => $this->validationErrors($request),
            'resource' => $request->isFragmentRequest()
                ? $request->resolveFragment()->metaValue('resource')
                : null,
        ]);
    }  

    public function profileFilled($request) {
        if ($this->metaValue('profile.guarded.avatar') &&
            is_null($request->user()->media)
            ) {
            return false;
        }

        $callback = function($value, $key) use ($request) {
            $profileValue = data_get($request->user()->profile, $key);

            return $value && blank($profileValue);
        };
       
        return collect($this->metaValue('profile.guarded'))->filter($callback)->isEmpty();
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
            Select::make(__('Display Profile Error By'), 'config->profile->error_view')
                ->options(Gutenberg::cachedTemplates()->forHandler(Blank::class)->keyBy->getKey()->map->name)  
                ->displayUsingLabels()
                ->required()
                ->rules('required'), 

            BooleanGroup::make(__('Required profile'), 'config->profile->guarded')->options([
                'firstname' => __('First Name'),
                'lastname' => __('Last Name'),
                'mobile' => __('Mobile Number'),
                'phone' => __('Phone Number'),
                'avatar' => __('Avatar'),
                'gender' => __('Gender'),
            ]),
        ];
    }

    /**
     * Serialize the widget fro template.
     * 
     * @return array
     */
    public function serializeForDisplay(): array
    {    
        $resourceId = $this->metaValue('resource.id');
        $amenities = Amenity::newModel()->get()->map(function($amenity) { 
            $attached = collect($this->metaValue('resource.amenities'))
                ->first(function($attached) use ($amenity) {
                    return $attached->is($amenity);
                });

            $oldValue = is_array(old("amenity.{$amenity->getKey()}"))
                ? collect(old('amenities'))->contains($amenity->getKey())
                : old("amenity.{$amenity->getKey()}");

            return array_merge($amenity->serializeForWidget($this->getRequest()), [
                'id' => $amenity->getKey(),
                'field' => $amenity->field, 
                'value' => $oldValue ?: data_get($attached, 'pivot.value'), 
                'active' => ! is_null($attached),
            ]);
        }); 

        return [
            'property' => (array) optional($this->metaValue('resource'))->toArray(),  
            'oldIamges' => with($this->metaValue('resource'), function($property) {
                if (! $property) return [];

                return $property->media->keyBy->getKey()->map->getUrl()->all();
            }),
            'storeUrl' => $resourceId 
                ? route('iranmoble.property.update', $resourceId) 
                : route('iranmoble.property.store') , 
            'csrf_token' => csrf_token(),
            'errors' => (array) $this->metaValue('errors'),
            'propertyTypes' => PropertyType::newModel()->get(),
            'roomTypes' => RoomType::newModel()->get(),
            'paymentBasis' => PaymentBasis::newModel()->get(),
            'reservations' => Reservation::newModel()->get(),
            'pricings' => Pricing::newModel()->get()->map(function($pricing) {
                $attached = collect($this->metaValue('resource.pricings'))
                    ->first(function($attached) use ($pricing) {
                        return $attached->is($pricing);
                    });

                return [
                    'id' => $pricing->getKey(),
                    'name' => $pricing->name,
                    'value' => old("pricing.{$pricing->getKey()}.value", data_get($attached, 'pivot.amount')),
                ];
            }),
            'conditions' => Condition::newModel()->get()->map(function($condition) {
                $attached = collect($this->metaValue('resource.conditions'))
                    ->first(function($attached) use ($condition) {
                        return $attached->is($condition);
                    });

                return array_merge($condition->toArray(), [
                    'active' => old("conditions.{$condition->getKey()}", ! is_null($attached)),
                ]);
            }),
            'states' => State::newModel()->get(),
            'cities' => City::newModel()->get(),
            'zones' => Zone::newModel()->get(),
            'details' => $amenities->keyBy('id'),
            'groupedDetails' => $amenities->groupBy('group_id'), 
            'availableDetails' => $amenities->where('field', 'boolean'),
            'countableDetails' => $amenities->where('field', 'number'),
            'descriptiveDetails' => $amenities->where('field', 'text'),
            'old' => session()->getOldInput(),
            'success' => session('success') === true,
            'message' => session('message'),
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
            \Armincms\Iranmobleh\Gutenberg\Templates\EditMyPropertyWidget::class
        );
    }

    /**
     * Request validation errors.
     * 
     * @param  CypressRequest $request 
     * @return array                  
     */
    protected function validationErrors(CypressRequest $request)
    {
        if (is_null($errors = $request->session()->get('errors'))) {
            return [];
        }

        return collect($errors->messages())->map(function($errors, $field) {
            return $errors[0] ?? null;
        })->toArray();
    }
}
