<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Increase PHP execution limits (upload_max_filesize and post_max_size must be set in php.ini)
@ini_set('max_execution_time', '300');
@ini_set('max_input_time', '300');
@ini_set('memory_limit', '256M');

// Suppress "Broken pipe" notices from development server (harmless - client disconnects early)
// This happens when Laravel's dev server tries to log to stdout but the pipe is closed
// Use both error handler and error reporting suppression
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Suppress "Broken pipe" notices from file_put_contents in server.php
    if ($errno === E_NOTICE && 
        (strpos($errstr, 'file_put_contents') !== false || strpos($errstr, 'Write of') !== false) && 
        (strpos($errstr, 'Broken pipe') !== false || strpos($errstr, 'errno=32') !== false) &&
        (strpos($errfile, 'server.php') !== false || strpos($errfile, 'Illuminate') !== false)) {
        return true; // Suppress this specific error
    }
    // Let other errors through to default handler
    return false;
}, E_ALL);

// Suppress notices in error reporting for development (this is the most effective)
if (env('APP_ENV') === 'local' || env('APP_DEBUG', false)) {
    // E_STRICT is deprecated in PHP 8.1+ and merged into E_ALL, so we don't need it
    // Completely suppress notices - they're usually not critical in development
    error_reporting(E_ALL & ~E_NOTICE);
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Enable Sanctum stateful authentication for API routes
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
