<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\OrderController;
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
Route::put('update/profile', [AuthController::class, 'update']);

Route::prefix('auth/google')->group(function () {
    Route::get('/redirect', [AuthenticationController::class, 'redirect'])->name('auth.redirect');
    Route::get('/callback', [AuthenticationController::class, 'callback'])->name('auth.callback');
});

Route::prefix('role-permissions')->middleware(["auth:sanctum", "role:admin"])->group(function () {
    Route::post('/assign-permission/{roleId}', [PermissionsController::class, 'assignPermissions']);
});

Route::prefix('user-manager')->middleware(["auth:sanctum", "role:admin"])->group(function () {
    Route::put('update-role/{userId}', [UserMangerContoller::class, 'updateRole'])->whereUuid('userId');
    Route::delete('delet-user/{userId}', [UserMangerContoller::class, 'deleteUser'])->whereUuid('userId');
    Route::get('all-user', [UserMangerContoller::class, 'getUser']);

});


Route::prefix('home')->name('home.')->group(function () {
    require __DIR__ . '/home.php';
});

Route::prefix('cities')->group(function () {
    Route::get('/', [CityController::class, 'index']);
    Route::get('/{cityId}/municipality', [CityController::class, 'getMunicipalityByCity']);
});

Route::prefix('orders')->group(function () {
    Route::apiResource('/', OrderController::class, ['as' => 'order'])->parameters(['' => 'order']);
});
Route::prefix("settings")->middleware(["auth:sanctum", "role:admin"])->group(function () {
    require __DIR__ . '/setting.php';
});
Route::prefix('users')->middleware(['auth:sanctum'])->group(function () {
    require __DIR__ . '/users.php';
});
Route::prefix("admin")->middleware(["auth:sanctum", "role:admin"])->name('admin.')->group(function () {
    require __DIR__ . '/admin.php';
});






