<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$routeHandlers = [
    new \App\Routes\UserRoutes(),
    new \App\Routes\PostRoutes(),
    new \App\Routes\CategoryRoutes(),
    new \App\Routes\LoginRoutes()
];

$auth = new \App\Auth\Authorization();

$matchFound = false;

foreach ($routeHandlers as $routeHandler) {
    if ($routeHandler->handleRequest()) {
        $matchFound = true;
        break;
    }
}

if (!$matchFound) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['message' => 'No route found for this URL']);
    exit();
}

/* 
    CREATE ABSTRACT CONTROLLER 
        class UserController extends AbstractController 
        class PostController extends AbstractController 

    CREATE ROUTER CLASS
        class UserRoutes extends Router
        class PostRoutes extends Router

    Database Singleton
        https://refactoring.guru/design-patterns/singleton/php/example

    JWT
        https://lcobucci-jwt.readthedocs.io/en/stable/quick-start/
*/

