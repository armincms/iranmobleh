<?php

use Armincms\Iranmobleh\Http\Controllers\PropertyController; 
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::post('/properties', PropertyController::class.'@store')->name('iranmoble.property.store');
Route::put('/properties/{property}', PropertyController::class.'@update')->name('iranmoble.property.update');
Route::delete('/properties/{property}', PropertyController::class.'@delete')->name('iranmoble.property.delete'); 
Route::post('/properties/{property}/promotion', PropertyController::class.'@promotion')->name('iranmoble.property.promotion');  