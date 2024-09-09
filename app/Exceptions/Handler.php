<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use InvalidArgumentException;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use App\Http\Responses\ApiResponse;

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
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            // Customize the JSON response for unauthenticated users
            return ApiResponse::error('Unauthenticated', 401, 'Please login first');
        }
    }
    public function render($request, Throwable $exception)
    {
        // Handle Not Found exceptions
        /*if ($exception instanceof ModelNotFoundException) {
            return response()->json(['message' => 'Resource not found', 'status' => 404], 404);
        }*/

        // Handle Invalid Argument exceptions
        /*if ($exception instanceof InvalidArgumentException) {
            return response()->json([
                'message' => 'An invalid argument was provided. Please check your input and try again.',
                'status' => 400
            ], 400); // Return a 400 Bad Request response
        }*/

        // Handle other exceptions
        return parent::render($request, $exception); // Default handling for other exceptions
    }
}
