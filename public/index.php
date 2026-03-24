<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Ensure runtime-writable storage directories exist in ephemeral environments (e.g. Railway).
$requiredStorageDirectories = [
    __DIR__.'/../storage/framework',
    __DIR__.'/../storage/framework/cache',
    __DIR__.'/../storage/framework/sessions',
    __DIR__.'/../storage/framework/testing',
    __DIR__.'/../storage/framework/views',
    __DIR__.'/../storage/logs',
];

foreach ($requiredStorageDirectories as $directory) {
    if (! is_dir($directory)) {
        @mkdir($directory, 0775, true);
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
