<?php

namespace App\Exceptions;

use Dotenv\Exception\ValidationException;
use Exception;
use Throwable;

class Handler extends Exception
{
    //
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

    
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $e->validator->errors()
                ], 422);
            }
        });
    }
}
