<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServiceRequestsController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\SlideController;
use App\Http\Controllers\StatusController;


route::prefix('orders')->group(function () {
    Route::patch('validate/{id}', [OrderController::class, 'validate']);
    Route::patch('canceled/{id}', [OrderController::class, 'canceled']);
    Route::patch('deliver/{id}', [OrderController::class, 'markAsDelivered']);
    Route::get('orders-validated', [OrderController::class, 'getValidatedOrders']);
    Route::get('orders-delivered', [OrderController::class, 'getDeliveredOrders']);
    Route::get('orders-pending', [OrderController::class, 'getPendingOrders']);
    Route::get('orders-canceled', [OrderController::class, 'getCancelledOrders']);
    Route::get('count', [OrderController::class, 'countOrders']);
    // payment
    // Route::post('/pay', [OrderController::class, 'redirectToGateway'])->name('pay');
});

// Route payment.callback accessible sans préfixe
Route::get('/payment/callback', [OrderController::class, 'handleGatewayCallback'])->name('payment.callback');

Route::prefix('services')->group(function () {
    Route::apiResource('/', ServicesController::class, ['as' => 'service'])->parameters(['' => 'service']);
});

Route::prefix('service-requests')->group(function () {
    Route::get('/', [ServiceRequestsController::class, 'index']);
    Route::get('/{request}', [ServiceRequestsController::class, 'show'])->whereUuid('request');
    Route::delete('{request}', [ServiceRequestsController::class, 'destroy'])->whereUuid('request');
    Route::patch('completed/{request}', [ServiceRequestsController::class, 'validateRequest'])->whereUuid('request');
    Route::patch('reject/{request}', [ServiceRequestsController::class, 'rejectRequest'])->whereUuid('request');
    Route::get('completed', [ServiceRequestsController::class, 'getCompletedRequests']);
    Route::get('rejected', [ServiceRequestsController::class, 'getRejectedRequests']);
    Route::get('pending', [ServiceRequestsController::class, 'getPendingRequests']);
    Route::get('count', [ServiceRequestsController::class, 'countServiceRequests']);
});

Route::prefix('cities')->group(function () {
    Route::get('/{cityId}/municipality', [CityController::class, 'getMunicipalityByCity']);
    Route::apiResource('/', CityController::class, ['as' => 'city'])->parameters(['' => 'city']);
});
Route::prefix('categories')->middleware(["auth:sanctum", "role:admin"])->group(function () {
    Route::get('{categorie}', [CategoriesController::class, 'sousCategories'])->whereUuid('categorie');
    Route::get('all/categories', [CategoriesController::class, 'getCategories']);
    Route::get('parent/categories', [CategoriesController::class, 'parentCategories'])->whereUuid('categorie');
    Route::apiResource('/', CategoriesController::class, ['as' => 'categorie'])->parameters(['' => 'categorie']);
});

Route::prefix('products')->middleware(["auth:sanctum", "role:admin"])->group(function () {
    Route::get('media/product/{product}', [ProductController::class, 'getMedia']);
    Route::post('create-media/product/{product}', [ProductController::class, 'AddMedia']);
    Route::post('update-media/product/{product}/media/{mediaId}', [ProductController::class, 'UpdateMedia']);
    Route::delete('delete-media/product/{product}/media/{mediaId}', [ProductController::class, 'delteMedia']);
    Route::get("available", [ProductController::class, 'getAvailableProducts']);
    Route::get("out-of-stock", [ProductController::class, 'getOutOfProducts']);
    Route::get("count", [ProductController::class, 'countProducts']);
    Route::apiResource('/', ProductController::class, ['as' => 'product'])->parameters(['' => 'product']);
});

Route::prefix('statutes')->group(function () {
    Route::apiResource('/', StatusController::class, ['as' => 'status'])->parameters(['' => 'status']);
});
Route::prefix('slides')->group(function () {
    Route::patch('enable/{slide}', [SlideController::class, 'enable']);
    Route::patch('disable/{slide}', [SlideController::class, 'disable']);
    Route::apiResource('/', SlideController::class, ['as' => 'slide'])->parameters(['' => 'slide']);
});

Route::prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
});

Route::prefix('projects')->group(function () {
    Route::apiResource('/', ProjectController::class, ['as' => 'project'])->parameters(['' => 'project']);
});
