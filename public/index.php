<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// A leftover maintenance file returns 503 before Laravel starts.
// Remove it so a git pull can bring the site back without a terminal.
$maintenance = __DIR__.'/../storage/framework/maintenance.php';
if (file_exists($maintenance)) {
    @unlink($maintenance);
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
