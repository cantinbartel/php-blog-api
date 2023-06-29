<?php

namespace App\Routes;

abstract class Router {
    // The keys are the HTTP methods 
    // The values are arrays which will contain the routes
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
    ];

    // Handles incoming requests
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = trim($path, "/");

        // Checks if there are any routes defined for the HTTP method of the request
        if (!isset($this->routes[$method])) {
            $this->sendResponse(['message' => 'Method Not Allowed'], 405);
            return false;
        }

        // For each route, it checks if the route matches the requested path
        foreach ($this->routes[$method] as $route => $action) {
            if (preg_match('~^' . $route . '$~i', $path, $params)) {
                array_shift($params);
                echo $action(...$params);
                return true;
            }
        }

        return false;
    }

    protected function sendResponse($data, $statusCode) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
}
