<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\User\UserMangerContoller;
Route::prefix('carts')->group(function(){
    Route::post('/clear',[CartController::class,'clear']);
    Route::get('count',[CartController::class,'countCart']);
 Route::apiResource('/',CartController::class,['as'=>'cart'])->parameters([''=>'cart']);
});

Route::prefix('infos')->group(function(){
Route::get('/profile',[UserMangerContoller::class,'profile']);
Route::get('/orders',[OrderController::class,'userOrders']);
});