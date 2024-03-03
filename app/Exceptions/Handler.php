<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }


    public function render($request, Throwable $exception)
    {
        if ($request->wantsJson()) {

            if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                    'error' => $exception->getMessage(),
                ], 401); // Return a 401 Unauthorized status code for authentication errors
            }

            if ($exception instanceof ModelNotFoundException) { // Check if this is a ModelNotFoundException
                return response()->json([
                    'status' => false,
                    'message' => 'Resource not found', // Custom message for not found resources
                    'error' => $exception->getMessage(),
                ], 404); // HTTP status code for Not Found
            }

            return response()->json([
                'status' => false,
                'message' => 'Oops! Something went wrong.', // Feel free to customize
                'error' => $exception->getMessage(), // You might want to hide or customize this for production
            ], 500); // Consider adjusting the status code based on the exception
        }
        return parent::render($request, $exception);
    }
}
