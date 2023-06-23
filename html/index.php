<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// $jwtMiddleware = new \App\Middleware\JwtMiddleware(getenv('JWT_SECRET'));

// User routes
require_once __DIR__ . '/src/Routes/UserRoutes.php';



