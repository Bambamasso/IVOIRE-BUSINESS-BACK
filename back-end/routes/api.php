<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\User\UserMangerContoller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');


Route::prefix('role-permissions')->middleware(["auth:sanctum", "role:admin"])->group(function () {
    Route::post('/assign-permission/{roleId}', [PermissionsController::class, 'assignPermissions']);
});

Route::prefix('user-manager')->middleware(["auth:sanctum", "role:admin"])->group(function () {
    Route::put('update-role/{userId}', [UserMangerContoller::class, 'updateRole'])->whereUuid('userId');
    Route::delete('delet-user/{userId}',[UserMangerContoller::class,'deleteUser'])->whereUuid('userId');
    Route::get('all-user',[UserMangerContoller::class,'getUser']);

});

Route::prefix('categories')->middleware(["auth:sanctum","role:admin"])->group(function(){
    Route::get('{categorie}',[CategoriesController::class,'sousCategories'])->whereUuid('categorie');
    Route::get('parent/categories',[CategoriesController::class,'parentCategories'])->whereUuid('categorie');
    Route::apiResource('/', CategoriesController::class, ['as' => 'categorie'])->parameters([''=>'categorie']);
});

Route::prefix('products')->middleware(["auth:sanctum","role:admin"])->group(function(){
    Route::get('media/product/{product}', [ProductController::class, 'getMedia']);
    Route::post('creat-media/product/{product}',[ProductController::class,'AddMedia']);
    Route::post('update-media/product/{product}/media/{mediaId}',[ProductController::class,'UpdateMedia']);
    Route::delete('delete-media/product/{product}/media/{mediaId}',[ProductController::class,'delteMedia']);
    Route::apiResource('/',ProductController::class,['as'=>'product'])->parameters([''=>'product']);
});

route::prefix('home')->group(function(){
   require __DIR__.'/home.php'; 
});

Route::prefix('users')->middleware(['auth:sanctum'])->group(function(){
   require __DIR__.'/users.php';
});




