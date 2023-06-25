<?php

$UserController = new \App\Controllers\UserController;
$postController = new \App\Controllers\PostController;

// Get the HTTP method, path, and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, "/");

$routes = [
    'GET' => [
        'posts' => function() use ($postController) {
            return $postController->index();
        },
        'posts/([a-f0-9\-]+)' => function($id) use ($postController) {
            return $postController->show($id);
        },
    ],
    'POST' => [
        'posts' => function() use ($postController) {
            return $postController->store();
        }
    ],
    'PUT' => [
        'posts/([a-f0-9\-]+)' => function($id) use ($postController) {
            return $postController->update($id);
        }
    ],
    'DELETE' => [
        'posts/([a-f0-9\-]+)' => function($id) use ($postController) {
            return $postController->destroy($id);
        }
    ],
];

if (!isset($routes[$method])) {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit();
}

foreach ($routes[$method] as $route => $action) {
    if (preg_match('~^' . $route . '$~i', $path, $params)) {
        array_shift($params); // remove the first match which is the whole url
        echo $action(...$params); // call the callback with the remaining url parts as parameters
        exit();
    }
}

http_response_code(404);
echo json_encode(['message' => 'No route found for this URL']);
