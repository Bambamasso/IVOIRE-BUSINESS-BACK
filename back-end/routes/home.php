<?php
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CategoriesController::class, 'index']);
Route::get('all-product',[ProductController::class,'index']);
Route::get('product/{product}',[ProductController::class,'show'])->whereUuid('product');