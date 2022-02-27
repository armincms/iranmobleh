<?php

namespace Armincms\Iranmobleh\Http\Controllers; 

use Armincms\Iranmobleh\Cypress\Fragments\PropertyForm;   
use Armincms\Iranmobleh\Http\Requests\StoreRequest; 
use Armincms\Iranmobleh\Http\Requests\UpdateRequest;
use Zareismail\Gutenberg\Gutenberg;

class PropertyController extends Controller
{
    public function store(StoreRequest $request)
    {
        $resource = $request->newModel()->forceFill($request->getPropertyAttributes());
        $resource->save();

        if ($request->hasFile(['images'])) { 
            $images = collect($request->file('images'))->map(function($file, $key) {
                return "images.{$key}";
            });
            $resource->addMultipleMediaFromRequest($images->values()->all())->each->toMediaCollection('gallery');
        }

        $editFramgment = Gutenberg::cachedFragments()->forHandler(PropertyForm::class)->first();

        return redirect()->to($editFramgment->getUrl($resource->getKey()))->with([
            'success' => true,
            'message' => __('Your data was stored')
        ]); 
    }

    public function update(UpdateRequest $request)
    {
        $resource = $request->findResource()->forceFill($request->getPropertyAttributes());
        $resource->save();

        if ($request->hasFile('images')) {  
            $images = collect($request->file('images'))->map(function($file, $key) {
                return "images.{$key}";
            });
            $resource->addMultipleMediaFromRequest($images->values()->all())->each->toMediaCollection('gallery');
        }

        return back()->with([
            'success' => true,
            'message' => __('Your data was stored')
        ]);
    }
}