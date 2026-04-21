<?php

use App\Http\Controllers\AttributesController;

Route::prefix('attributes')->group(function () {
    Route::get('values/{attribute}', [AttributesController::class, 'getAttributeValues']);
    Route::apiResource('/', AttributesController::class, ["as" => "attribute"])->parameters(["" => "attribute"]);
});


