<?php
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ServiceRequestsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CategoriesController::class, 'index']);
Route::get('all-product',[ProductController::class,'index']);
Route::get('product/{product}',[ProductController::class,'show'])->whereUuid('product');

Route::prefix('statuses')->group(function(){
Route::apiResource('/',StatusController::class,['as'=>'status'])->parameters([''=>'status']);
});

route::prefix('requests-service')->group(function(){
   Route::apiResource('/',ServiceRequestsController::class,['as'=>'request'])->parameters([''=>'request']);
});

Route::get('all/services', [ServicesController::class, 'getServices']);