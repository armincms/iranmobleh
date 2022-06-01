<?php

namespace Armincms\Iranmobleh\Http\Controllers;

use Armincms\Iranmobleh\Cypress\Fragments\PropertyForm;
use Armincms\Iranmobleh\Http\Requests\DeleteRequest;
use Armincms\Iranmobleh\Http\Requests\StoreRequest;
use Armincms\Iranmobleh\Http\Requests\PromotionRequest;
use Armincms\Iranmobleh\Http\Requests\UpdateRequest;
use Armincms\Koomeh\Nova\Promotion;
use Armincms\Orderable\Nova\Order;
use Zareismail\Gutenberg\Gutenberg;

class PropertyController extends Controller
{
    public function store(StoreRequest $request)
    {
        $resource = $request
            ->newModel()
            ->forceFill($request->getPropertyAttributes());
        $resource->save();

        $resource->amenities()->sync($request->prepareAmenitiesForStorage());
        $resource->conditions()->sync((array) $request->get("conditions"));
        $resource->pricings()->sync((array) $request->get("pricing"));

        if ($request->hasFile(["images"])) {
            $images = collect($request->file("images"))->map(function (
                $file,
                $key
            ) {
                return "images.{$key}";
            });
            $resource
                ->addMultipleMediaFromRequest($images->values()->all())
                ->each->toMediaCollection("gallery");
        }

        $editFramgment = Gutenberg::cachedFragments()
            ->forHandler(PropertyForm::class)
            ->first();

        return redirect()
            ->to($editFramgment->getUrl($resource->getKey()))
            ->with([
                "success" => true,
                "message" => __("Your data was stored"),
            ]);
    }

    public function update(UpdateRequest $request)
    {
        $resource = $request
            ->findResource()
            ->forceFill($request->getPropertyAttributes());
        $resource->save();

        $resource->amenities()->sync($request->prepareAmenitiesForStorage());
        $resource->conditions()->sync((array) $request->get("conditions"));
        $resource->pricings()->sync((array) $request->get("pricing"));

        $resource->media->each(function ($media) use ($request) {
            if (collect($request->get("oldIamges"))->doesntContain($media->getKey())) {
                $media->delete();
            }
        });

        if ($request->hasFile("images")) {
            $images = collect($request->file("images"))->map(function ($file, $key) {
                return "images.{$key}";
            });

            $resource
                ->addMultipleMediaFromRequest($images->values()->all())
                ->each->toMediaCollection("gallery");
        }

        return back()->with([
            "success" => true,
            "message" => __("Your data was stored"),
        ]);
    }

    public function delete(DeleteRequest $request)
    {
        $request->findResource()->delete();

        return back()->with([
            "success" => true,
            "message" => __("Your data was deleted"),
        ]);
    }

    public function promotion(PromotionRequest $request)
    {
        $order = tap(Order::newModel(), function ($order) use ($request) {
            $promotion = Promotion::newModel()->findOrFail($request->promotion);

            $order
                ->forceFill([
                    "name" => $promotion->name,
                    "resource" => Promotion::class,
                    "callback_url" => \URL::previous(),
                ])
                ->asOnHold();

            $order->addItem($promotion, ['property' => $request->property]);
        });

        return $order->redirect($request);
    }
}
