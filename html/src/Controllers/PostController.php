<?php

namespace App\Controllers;

use App\Models\Post;

class PostController {

    // Get all posts
    public function index() {
        try {
            $posts = Post::getAll();
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode($posts);
        } catch(\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    // Get a post by id
    public function show($id) {
        try {
            $post = Post::find($id);
            if ($post) {
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode($post);
            } else {
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode(['error' => 'Post not found']);
            } 
        } catch(\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    // Get posts by category
    public function indexByCategory($categoryId) {
        try {
            $posts = Post::getPostsByCategory($categoryId);
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode($posts);
        } catch(\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    // Create a post
    public function store() {
        try {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
        
            $postData = [
                'title' => $data['title'],
                'content' => $data['content'],
                'user_id' => $data['user_id']
            ];
        
            $categoryData = ['category_id' => $data['category_id']];
        
            $post = Post::create($postData, $categoryData);
        
            header('Content-Type: application/json');
            http_response_code(201);
            echo json_encode($post);
        } catch(\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        } 
    }

    public function update($id) {
        try {
            // Get JSON input
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
    
            // Create postData with only the keys that are present in $data
            $postData = [];
            $keys = ['user_id', 'title', 'content'];
            foreach ($keys as $key) {
                if (isset($data[$key])) {
                    $postData[$key] = $data[$key];
                }
            }

            $categoryId = isset($data['category_id']) ? $data['category_id'] : null;

    
            // Update post
            Post::update($id, $postData, $categoryId);
            $post = Post::find($id);
    
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(['message' => 'Post updated', 'post' => $post]);
        } catch(\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
    

    // Delete a post
    public function destroy($id) {
        try {
            Post::delete($id);
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(['message' => 'Post deleted']);
        } catch(\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
}
