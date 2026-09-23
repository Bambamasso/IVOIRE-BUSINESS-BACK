<?php

use App\Http\Controllers\AttributesController;
use App\Http\Controllers\AttributeValueController;

Route::prefix('attributes')->group(function () {
    Route::get('values/{attribute}', [AttributesController::class, 'getAttributeValues']);
    Route::apiResource('/', AttributesController::class, ["as" => "attribute"])->parameters(["" => "attribute"]);
});

Route::prefix('attribute-values')->group(function () {
    Route::post('/', [AttributeValueController::class, 'store']);
    Route::put('/{attributeValue}', [AttributeValueController::class, 'update']);
    Route::delete('/{attributeValue}', [AttributeValueController::class, 'destroy']);
});


