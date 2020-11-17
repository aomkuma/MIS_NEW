<?php

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

 error_reporting(E_ERROR);
    error_reporting(E_ALL);
    ini_set('display_errors','On');

    

date_default_timezone_set("Asia/Bangkok");
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}


require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
/*
$database = array(
    'driver' => 'mysql',
    'database' => 'dpo',
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix'    => '',
);

use Illuminate\Database\Capsule\Manager as Capsule;
$capsule = new Capsule;
//$capsule->addConnection($database);
$capsule->addConnection($settings['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
*/
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

//require __DIR__ . '/../src/models/User.php';  
// Run app
$app->run();
