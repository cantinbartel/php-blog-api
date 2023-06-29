<?php

namespace App\Controllers;

use App\Models\Post;

class PostController extends BaseController {

    // Get all posts
    public function index() {
        try {
            $posts = Post::getAll();
            $this->jsonResponse($posts);
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }

    // Get a post by id
    public function show($id) {
        try {
            $post = Post::find($id);
            if ($post) {
                $this->jsonResponse($post);
            } else {
                $this->jsonError('Post not found', 404);
            } 
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }

    // Get posts by category
    public function indexByCategory($categoryId) {
        try {
            $posts = Post::getPostsByCategory($categoryId);
            $this->jsonResponse($posts);
        } catch(\Exception $e) {
            $this->jsonResponse('Database error: ' . $e->getMessage());
        }
    }

    // Create a post
    public function store() {
        try {
            $data = $this->getJsonInput();
        
            $postData = [
                'title' => $data['title'],
                'content' => $data['content'],
                'user_id' => $data['user_id']
            ];
        
            $categoryData = ['category_id' => $data['category_id']];
        
            $result = Post::create($postData, $categoryData);

            $this->jsonResponse(['message' => 'Post created', 'result' => $result], 201);
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        } 
    }

    public function update($id) {
        try {
            // Get JSON input
            $data = $this->getJsonInput();

            // Create postData with only the keys that are present in $data
            $postData = [];
            $keys = ['user_id', 'title', 'content'];
            foreach ($keys as $key) {
                if (isset($data[$key])) {
                    $postData[$key] = $data[$key];
                }
            }

            $categoryId = isset($data['category_id']) ? $data['category_id'] : null;

            Post::update($id, $postData, $categoryId);
            $post = Post::find($id);
            $this->jsonResponse($post);
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
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
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }
}
