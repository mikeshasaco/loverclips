<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Suppress harmless notices/warnings in development (keeps responses clean for Inertia)
// This prevents "Broken pipe" and other non-critical notices from polluting JSON responses
// Check environment variables directly (env() not available until Laravel loads)
$appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production';
$appDebug = isset($_ENV['APP_DEBUG']) ? filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN) : (filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN) ?: false);

if ($appEnv === 'local' || $appDebug) {
    error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
    ini_set('display_errors', '0'); // Log errors but don't display them (prevents JSON corruption)
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
