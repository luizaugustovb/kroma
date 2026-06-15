<?php
/**
 * Sistema de Rotas — KROMA PRINT ERP
 */

namespace App\Services;

class Router
{
    private array $routes = [];
    private string $basePath = '';

    public function __construct()
    {
        // Detecta o base path automaticamente
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $this->basePath = rtrim($scriptDir, '/');
    }

    /**
     * Registra uma rota GET
     */
    public function get(string $path, $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Registra uma rota POST
     */
    public function post(string $path, $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Registra rotas GET e POST
     */
    public function any(string $path, $handler): void
    {
        $this->addRoute('GET', $path, $handler);
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, $handler): void
    {
        $this->routes[] = [
            'method'  => $method,
            'path'    => $path,
            'handler' => $handler,
        ];
    }

    /**
     * Despacha a requisição para o handler correto
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = $_SERVER['REQUEST_URI'];

        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Remove o base path
        $uri = '/' . ltrim(substr($uri, strlen($this->basePath)), '/');
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $pattern = $this->pathToRegex($route['path']);
            if (preg_match($pattern, $uri, $matches)) {
                // Extrai parâmetros nomeados
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        // 404
        $this->notFound();
    }

    private function pathToRegex(string $path): string
    {
        $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function callHandler($handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$class, $method] = explode('@', $handler);
            $fullClass = 'App\\Controllers\\' . $class;
            $controller = new $fullClass();
            call_user_func_array([$controller, $method], $params);
            return;
        }

        $this->notFound();
    }

    private function notFound(): void
    {
        http_response_code(404);
        require APP_PATH . '/Views/errors/404.php';
        exit;
    }
}
