<?php
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
define('LARAVEL_START', microtime(true));
if (file_exists($maintenance = __DIR__.'/../vits/storage/framework/maintenance.php')) {
    require $maintenance;
}
require __DIR__.'/../vits/vendor/autoload.php';
$app = require_once __DIR__.'/../vits/bootstrap/app.php';
$app->handleRequest(Request::capture());
