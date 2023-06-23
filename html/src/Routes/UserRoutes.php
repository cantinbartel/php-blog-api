<?php

require_once __DIR__ . '/../Controllers/UserController.php';

$userController = new \App\Controllers\UserController;

// Get the HTTP method, path, and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, "/");

$routes = [
    'GET' => [
        'users' => function() use ($userController) {
            // echo json_encode([
            //     'message' => 'url/users',
            //     'MYSQL_DATABASE'=> getenv('MYSQL_DATABASE')
            // ]);
            return $userController->index();
        },
        'users/([a-f0-9\-]+)' => function($id) use ($userController) {
            // echo json_encode(['message' => "url/$id"]);
            return $userController->show($id);
        },
    ],
    'POST' => [
        'users' => function() use ($userController) {
            return $userController->store();
        }
    ],
    'PUT' => [
        'users/([a-f0-9\-]+)' => function($id) use ($userController) {
            return $userController->update($id);
        }
    ],
    'DELETE' => [
        'users/([a-f0-9\-]+)' => function($id) use ($userController) {
            return $userController->destroy($id);
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
