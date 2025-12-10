<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // Log all exceptions with context
            if (config('app.debug')) {
                \Log::error('Exception: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });

        // Handle 500 errors gracefully
        $this->renderable(function (Throwable $e, $request) {
            // Don't show detailed errors in production
            if (!config('app.debug') && app()->environment('production')) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'An error occurred. Please try again later.',
                        'error' => 'Server Error'
                    ], 500);
                }
                
                // For web requests, redirect to a safe page
                if ($request->is('dashboard') || $request->is('home')) {
                    return redirect('login')->with('error', __('An error occurred. Please try again.'));
                }
            }
        });
    }
}
