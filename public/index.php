<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Locate the private Laravel application directory outside web root
$corePath = is_dir(__DIR__ . '/../hydrox_dev_app')
    ? __DIR__ . '/../hydrox_dev_app'
    : __DIR__ . '/..';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $corePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $corePath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $corePath . '/bootstrap/app.php';

// Explicitly register public web directory
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
