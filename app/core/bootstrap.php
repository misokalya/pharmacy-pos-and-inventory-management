<?php
declare(strict_types=1);

// Error reporting
$appConfig = require __DIR__ . '/../../config/app.php';

if ($appConfig['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

date_default_timezone_set($appConfig['timezone']);

// Autoloader for /app namespace (App\)
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/../' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_name($appConfig['session_name']);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Shorthand globals
define('APP_PATH', dirname(__DIR__, 2));
define('VIEW_PATH', APP_PATH . '/views');
define('BASE_URL', $appConfig['url']);