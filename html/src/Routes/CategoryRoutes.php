<?php

$UserController = new \App\Controllers\UserController;
$postController = new \App\Controllers\PostController;
$categoryController = new \App\Controllers\CategoryController;

// Get the HTTP method, path, and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, "/");

$routes = [
    'GET' => [
        'categories' => function() use ($categoryController) {
            // echo json_encode([
            //     'message' => 'url/users',
            //     'MYSQL_DATABASE'=> getenv('MYSQL_DATABASE')
            // ]);
            return $categoryController->index();
        },
        'categories/([a-f0-9\-]+)' => function($id) use ($categoryController) {
            // echo json_encode(['message' => "url/$id"]);
            return $categoryController->show($id);
        },
        'categories/([a-f0-9\-]+)/posts' => function($categoryId) use ($postController) {
            // echo json_encode(['message' => "url/$id"]);
            return $postController->indexByCategory($categoryId);
        }
    ],
    'POST' => [
        'categories' => function() use ($categoryController) {
            return $categoryController->store();
        }
    ],
    'PUT' => [
        'categories/([a-f0-9\-]+)' => function($id) use ($categoryController) {
            return $categoryController->update($id);
        }
    ],
    'DELETE' => [
        'users/([a-f0-9\-]+)' => function($id) use ($categoryController) {
            return $categoryController->destroy($id);
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
