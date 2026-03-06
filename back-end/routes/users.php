<?php

use App\Http\Controllers\CartController;
Route::prefix('carts')->group(function(){
    Route::post('/clear',[CartController::class,'clear']);
    Route::get('count',[CartController::class,'countCart']);
 Route::apiResource('/',CartController::class,['as'=>'cart'])->parameters([''=>'cart']);
});