<?php

namespace Armincms\Iranmobleh\Cypress\Fragments;
 
use Armincms\Contract\Concerns\InteractsWithModel; 
use Zareismail\Cypress\Fragment; 
use Zareismail\Cypress\Contracts\Resolvable; 

class PropertyForm extends Fragment implements Resolvable
{   
    use InteractsWithModel;

    /**
     * Resolve the resoruce's value for the given request.
     *
     * @param  \Zareismail\Cypress\Http\Requests\CypressRequest  $request 
     * @return void
     */
    public function resolve($request): bool
    {
        if ($request->isComponentRequest()) {
            return false;
        }

        $segments = $request->segments(); 

        $resource = $this->newQuery($request)->find(array_pop($segments));

        abort_unless(
            ! $resource || $resource->auth->is($request->user()),
            403,
        );

        $this->withMeta(compact('resource'));

        return true;
    }

    /**
     * Get the resource Model class.
     * 
     * @return
     */
    public function model(): string
    {
        return \Armincms\Koomeh\Models\KoomehProperty::class;
    } 

    /**
     * Apply custom query to the given query.
     *
     * @param  \Zareismail\Cypress\Http\Requests\CypressRequest $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyQuery($request, $query)
    {
        return $query->authorize()->with([
            'propertyLocality', 
            'propertyType', 
            'roomType', 
            'paymentBasis', 
            'reservation', 
            'state',
            'city',
            'zone',
            'amenities',
            'conditions',
        ]);
    } 
}
