<?php

namespace App\Routes;

use App\Controllers\PostController;

class PostRoutes {
    public function __construct($app) {
        $postController = new PostController();

        // Get all posts
        $app->get('/posts', [$postController, 'index']);

        // Get a single post
        $app->get('/posts/{id}', [$postController, 'show']);

        // Create a new post
        $app->post('/posts', [$postController, 'store']);

        // Update a post
        $app->put('/posts/{id}', [$postController, 'update']);

        // Delete a post
        $app->delete('/posts/{id}', [$postController, 'destroy']);
    }
}
