<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ServiceRequestsController;
use App\Http\Controllers\ServicesController;

route::prefix('orders')->group(function () {
    Route::patch('validate/{id}', [OrderController::class, 'validate']);
    Route::patch('reject/{id}', [OrderController::class, 'reject']);
    Route::patch('deliver/{id}', [OrderController::class, 'markAsDelivered']);
    Route::get('orders-validated', [OrderController::class, 'getValidatedOrders']);
    Route::get('orders-rejected', [OrderController::class, 'getRejectedOrders']);
    Route::get('orders-delivered', [OrderController::class, 'getDeliveredOrders']);
    Route::get('orders-pending', [OrderController::class, 'getPendingOrders']);
    Route::get('orders-cancelled', [OrderController::class, 'getCancelledOrders']);
});

Route::prefix('services')->group(function () {
    Route::apiResource('/', ServicesController::class, ['as' => 'service'])->parameters(['' => 'service']);
});

Route::prefix('service-requests')->group(function () {
    Route::get('/', [ServiceRequestsController::class, 'index']);
    Route::get('/{request}', [ServiceRequestsController::class, 'show'])->whereUuid('request');
    Route::delete('{request}',[ServiceRequestsController::class, 'destroy'])->whereUuid('request');
    Route::patch('completed/{request}', [ServiceRequestsController::class, 'validateRequest'])->whereUuid('request');
    Route::patch('reject/{request}', [ServiceRequestsController::class, 'rejectRequest'])->whereUuid('request');
    Route::get('completed', [ServiceRequestsController::class, 'getCompleteddRequests']);
    Route::get('rejected', [ServiceRequestsController::class, 'getRejectedRequests']);
    Route::get('pending', [ServiceRequestsController::class, 'getPendingRequests']);
});

Route::prefix('cities')->group(function () {
    Route::get('/{cityId}/municipality', [CityController::class, 'getMunicipalityByCity']);
    Route::apiResource('/', CityController::class, ['as' => 'city'])->parameters(['' => 'city']);
});
Route::prefix('categories')->middleware(["auth:sanctum", "role:admin"])->group(function () {
    Route::get('{categorie}', [CategoriesController::class, 'sousCategories'])->whereUuid('categorie');
    Route::get('all/gategories', [CategoriesController::class, 'getCategories']);
    Route::get('parent/categories', [CategoriesController::class, 'parentCategories'])->whereUuid('categorie');
    Route::apiResource('/', CategoriesController::class, ['as' => 'categorie'])->parameters(['' => 'categorie']);
});
