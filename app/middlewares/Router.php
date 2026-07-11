<?php
// ============================================================
// FOKOS EVENTOS — Auth Middleware + Router
// ============================================================

class Router {
    private array $routes = [];

    public function get(string $path, $handler): void {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, $handler): void {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void {
        $routes = $this->routes[$method] ?? [];

        // Rota exata
        if (isset($routes[$uri])) {
            call_user_func($routes[$uri]);
            return;
        }

        // Rota com parâmetros dinâmicos /:id
        foreach ($routes as $route => $handler) {
            $pattern = preg_replace('/\/:([^\/]+)/', '/([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                call_user_func_array($handler, $matches);
                return;
            }
        }

        // 404
        http_response_code(404);
        if (isAjax()) {
            jsonResponse(['erro' => 'Rota não encontrada.'], 404);
        }
        include APP_PATH . '/views/404.php';
    }
}

// ---- Rate Limiter ----
class RateLimiter {
    public static function check(string $key, int $maxAttempts, int $window): bool {
        $sessionKey = 'rl_' . md5($key);
        $now = time();

        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = ['count' => 0, 'start' => $now];
        }

        $data = &$_SESSION[$sessionKey];

        if ($now - $data['start'] > $window) {
            $data = ['count' => 0, 'start' => $now];
        }

        $data['count']++;
        return $data['count'] <= $maxAttempts;
    }

    public static function reset(string $key): void {
        unset($_SESSION['rl_' . md5($key)]);
    }
}