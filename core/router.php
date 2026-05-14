<?php
class Router {
    private $routes = [];

    public function add($method, $route, $file) {
        $this->routes[] = ['method' => $method, 'route' => $route, 'file' => $file];
    }

    public function dispatch($requestMethod) {
        // We use a query parameter (?route=/path) to ensure routing works 
        // perfectly even on servers without Apache .htaccess URL rewriting.
        $path = $_GET['route'] ?? '/chat';

        foreach ($this->routes as $r) {
            if ($r['route'] === $path && ($r['method'] === $requestMethod || $r['method'] === 'ANY')) {
                require_once __DIR__ . '/../' . $r['file'];
                return;
            }
        }

        http_response_code(404);
        echo "<div style='display:flex; height:100vh; align-items:center; justify-content:center; background:#0f172a; color:#00f0ff; font-family:sans-serif;'>";
        echo "<h2>404 | API or View Route Not Found</h2></div>";
    }
}
?>