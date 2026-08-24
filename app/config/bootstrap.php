<?php

define('APP_ROOT', dirname(__DIR__));

if (file_exists(APP_ROOT . '/config/config.php')) {
    require_once APP_ROOT . '/config/config.php';
} else {
    require_once APP_ROOT . '/config/config.example.php';
}

$debug = defined('APP_DEBUG') && APP_DEBUG;
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');

$__scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', rtrim(dirname($__scriptDir), '/'));

spl_autoload_register(function ($class) {
    $paths = [
        APP_ROOT . '/models/' . $class . '.php',
        APP_ROOT . '/controllers/' . $class . '.php',
        APP_ROOT . '/config/' . $class . '.php',
        APP_ROOT . '/helpers/' . $class . '.php',
        APP_ROOT . '/services/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

require_once APP_ROOT . '/config/Auth.php';
Auth::start();

require_once APP_ROOT . '/views/helpers.php';
