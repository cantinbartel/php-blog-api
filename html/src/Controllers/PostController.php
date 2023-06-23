<?php

namespace App\Controllers;

use App\Models\Post;

class PostController {

    // Get all posts
    public function index() {
        $posts = Post::getAll();
        header('Content-Type: application/json');
        echo json_encode($posts);
    }

    // Get a post by id
    public function show($id) {
        $post = Post::find($id);
        header('Content-Type: application/json');
        echo json_encode($post);
    }

    // Create a new post
    public function store() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $postId = Post::create($data);
        $post = Post::find($postId);
        header('Content-Type: application/json');
        echo json_encode($post);
    }

    // Update a post
    public function update($id) {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        Post::update($id, $data);
        $post = Post::find($id);
        header('Content-Type: application/json');
        echo json_encode($post);
    }

    // Delete a post
    public function destroy($id) {
        Post::delete($id);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Post deleted']);
    }
}
