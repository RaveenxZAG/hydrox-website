<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Fail closed instead of falling back to an obsolete checkout or database.
$corePath = '/home/hydro851/repositories/hydrox-website-sqlite';
if (!is_file($corePath . '/vendor/autoload.php') || !is_file($corePath . '/bootstrap/app.php')) {
    http_response_code(503);
    exit('The website is temporarily unavailable.');
}
if (file_exists($maintenance = $corePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}
require $corePath . '/vendor/autoload.php';
/** @var Application $app */
$app = require_once $corePath . '/bootstrap/app.php';
$app->usePublicPath(__DIR__);
$app->handleRequest(Request::capture());
