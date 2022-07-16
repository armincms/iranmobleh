<?php

namespace Armincms\Iranmobleh\Http\Controllers;

use Armincms\Koomeh\Nova\PaymentBasis;
use Armincms\Koomeh\Nova\PropertyLocality;
use Armincms\Koomeh\Nova\PropertyType;
use Armincms\Koomeh\Nova\Reservation;
use Armincms\Koomeh\Nova\RoomType;
use Armincms\Location\Nova\Zone;
use Armincms\Iranmobleh\Cypress\Fragments\PropertyForm;
use Armincms\Iranmobleh\Http\Requests\DeleteRequest;
use Armincms\Iranmobleh\Http\Requests\StoreRequest;
use Armincms\Iranmobleh\Http\Requests\PromotionRequest;
use Armincms\Iranmobleh\Http\Requests\UpdateRequest;
use Armincms\Iranmobleh\Http\Requests\UploadRequest;
use Armincms\Koomeh\Nova\Promotion;
use Armincms\Orderable\Nova\Order;
use Zareismail\Gutenberg\Gutenberg;

class PropertyController extends Controller
{
    public function store(StoreRequest $request)
    {
        $zone = Zone::newModel()->with('city')->first();

        $resource = $request
            ->newModel()
            ->forceFill($request->getPropertyAttributes([
                'property_locality_id' => PropertyLocality::newModel()->first()->getKey(),
                'property_type_id' =>  PropertyType::newModel()->first()->getKey(),
                'room_type_id' =>  RoomType::newModel()->first()->getKey(),
                'payment_basis_id' => PaymentBasis::newModel()->first()->getKey(),
                'reservation_id' => Reservation::newModel()->first()->getKey(),
            ]));
        \Schema::disableForeignKeyConstraints();
        $resource->save();
        \Schema::enableForeignKeyConstraints();

        $editFramgment = Gutenberg::cachedFragments()
            ->forHandler(PropertyForm::class)
            ->first();

        return redirect()
            ->to($editFramgment->getUrl($resource->getKey()))
            ->with([
                "success"   => true,
                "message"   => __("Your data was stored"),
                "step"      => 1,
            ]);
    }

    public function update(UpdateRequest $request)
    {
        $resource = $request
            ->findResource()
            ->forceFill($request->getPropertyAttributes());
        $resource->save();

        if ($request->exists('amenities')) {
            $resource->amenities()->sync($request->prepareAmenitiesForStorage());
        }

        if ($request->exists('conditions')) {
            $resource->conditions()->sync((array) $request->get("conditions"));
        }

        if ($request->exists('pricing')) {
            $resource->pricings()->sync((array) $request->get("pricing"));
        }

        return [
            "success" => true,
            "message" => __("Your data was stored"),
        ];
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

    public function upload(UploadRequest $request)
    {
        $request->findResource()->addMultipleMediaFromRequest(['file'])->map->toMediaCollection('gallery');

        return [
            'success' => true,
            'message' => __('Files uploaded successfully'),
            'media' => $this->gallery($request)->toJson(),
        ];
    }

    public function deleteMedia(UpdateRequest $request)
    {
        $request->findResource()->media->each(function ($media) use ($request) {
            if ($media->getKey() == $request->route('media')) {
                $media->delete();
            }
        });

        return [
            'success' => true,
            'message' => __('File removed successfully'),
            'media' => $this->gallery($request)->toJson(),
        ];
    }

    public function promotionMedia(UpdateRequest $request)
    {
        $request->findResource()->media->each(function ($media) use ($request) {
            $media->forceFill([
                'order_column' => $media->getKey() == $request->route('media') ? 0 : $media->getKey(),
            ]);

            $media->save();
        });

        return [
            'success' => true,
            'message' => __('File updated successfully'),
            'media' => $this->gallery($request)->toJson(),
        ];
    }

    public function gallery($request)
    {
        return $request->findResource()->media->sortBy('order_column')->values()->map(function($media) {
            return [
                'id' => $media->getKey(),
                'url' => $media->getUrl(),
                'order' => $media->order_column,
            ];
        });
    }
}
