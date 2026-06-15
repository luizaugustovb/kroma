<?php
/**
 * KROMA PRINT ERP/CRM
 * Entry Point principal do sistema
 * 
 * Toda requisição passa por aqui antes de ser roteada
 */

define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Inicia sessão de forma segura
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => false, // true em produção com HTTPS
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// Carrega configurações
require_once CONFIG_PATH . '/app.php';
require_once CONFIG_PATH . '/database.php';

// Autoloader simples PSR-4
spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\Controllers\\'  => APP_PATH . '/Controllers/',
        'App\\Models\\'       => APP_PATH . '/Models/',
        'App\\Services\\'     => APP_PATH . '/Services/',
        'App\\Middleware\\'   => APP_PATH . '/Middleware/',
        'App\\Helpers\\'      => APP_PATH . '/Helpers/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strncmp($prefix, $class, strlen($prefix)) === 0) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});

// Carrega o roteador e despacha a requisição
require_once APP_PATH . '/Services/Router.php';
$router = new App\Services\Router();
require_once APP_PATH . '/routes.php';
$router->dispatch();
