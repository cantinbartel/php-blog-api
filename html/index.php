<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// var_dump($_ENV);

// $jwtMiddleware = new \App\Middleware\JwtMiddleware(getenv('JWT_SECRET'));

// User routes
require_once __DIR__ . '/src/Routes/UserRoutes.php';
require_once __DIR__ . '/src/Routes/PostRoutes.php';
require_once __DIR__ . '/src/Routes/CategoryRoutes.php';


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

