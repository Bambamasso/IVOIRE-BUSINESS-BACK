
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::get('/payment/callback', [OrderController::class, 'handleGatewayCallback'])->name('payment.callback');

