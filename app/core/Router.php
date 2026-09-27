<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, callable|array $handler, array $middleware): void
    {
        $this->routes[] = [
            'method'     => $method,
            'path'       => trim($path, '/'),
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = trim(parse_url($uri, PHP_URL_PATH) ?? '', '/');
        // Strip base path if app is in subfolder (e.g. /pharmacy/public)
        $base = trim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');
        if ($base && str_starts_with($uri, $base)) {
            $uri = trim(substr($uri, strlen($base)), '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $route['path']);
            if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
                // Run middleware
                foreach ($route['middleware'] as $mw) {
                    $mwClass = "App\\Middleware\\{$mw}";
                    if (class_exists($mwClass)) {
                        (new $mwClass())->handle();
                    }
                }

                // Named params
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $methodName] = $handler;
                    $controller = new $class();
                    $controller->$methodName(...array_values($params));
                } else {
                    $handler(...array_values($params));
                }
                return;
            }
        }

        http_response_code(404);
        require VIEW_PATH . '/errors/404.php';
    }
}