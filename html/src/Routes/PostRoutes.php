<?php

namespace App\Routes;
use App\Routes\Router;
use App\Controllers\PostController;

class PostRoutes extends Router {
    function __construct() {
        $postController = new PostController();

        $this->routes = [
            'GET' => [
                'posts' => [$postController, 'index'],
                'posts/([a-f0-9\-]+)' => [$postController, 'show'],
                'posts/categories/([a-f0-9\-]+)' => [$postController, 'indexByCategory'],
                'posts/users/([a-f0-9\-]+)' => [$postController, 'indexByUser']
            ],
            'POST' => [
                'posts' => [$postController, 'store']
            ],
            'PUT' => [
                'posts/([a-f0-9\-]+)' => [$postController, 'update']
            ],
            'DELETE' => [
                'posts/([a-f0-9\-]+)' => [$postController, 'destroy']
            ]
        ];
    }
}
