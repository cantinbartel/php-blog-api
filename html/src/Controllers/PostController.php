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

    // Get posts by category
    public function indexByCategory($categoryId) {
        $posts = Post::getPostsByCategory($categoryId);
        header('Content-Type: application/json');
        echo json_encode($posts);
    }

    // Create a post
    public function store() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
    
        $postData = [
            'title' => $data['title'],
            'content' => $data['content'],
            'user_id' => $data['user_id']
        ];
    
        $categoryData = [
            'category_id' => $data['category_id']
        ];
    
        $post = Post::create($postData, $categoryData);
    
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
