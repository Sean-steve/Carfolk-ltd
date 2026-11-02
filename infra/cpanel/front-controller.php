<?php
// Public-only Laravel entry point for cPanel. All application code is outside public_html.
declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));
$api = dirname(__DIR__).'/carfolk/current/apps/api';

if (file_exists($maintenance = $api.'/storage/framework/maintenance.php')) {
    require $maintenance;
}
require $api.'/vendor/autoload.php';
/** @var Application $app */
$app = require $api.'/bootstrap/app.php';
$app->handleRequest(Request::capture());
