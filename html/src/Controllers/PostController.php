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

    // Get posts by user
    public function indexByUser($userId) {
        try {
            $posts = Post::getPostsByUser($userId);
            $this->jsonResponse($posts);
        } catch(\Exception $e) {
            $this->jsonResponse('Database error: ' . $e->getMessage());
        }
    }

    // Create a post
    public function store() {
        try {
            // Get JSON input
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

            $post = Post::find($id);
    
            if (!$post) {
                $this->jsonError('Post not found', 404);
            }

            // Only Admin or User who wrote the post are allowed to modify it  
            $userJwtData = $this->checkAuthorization();
            if ($userJwtData->role === self::ADMIN || $userJwtData->userId === $post['user_id']) {
                Post::update($id, $postData, $categoryId);
                $this->jsonResponse(['message' => 'Post updated']);
            } else {
                $this->jsonError('Not authorized', 403);
            } 
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }

    // Delete a post
    public function destroy($id) {
        try {
            $post = Post::find($id);
            if (!$post) {
                $this->jsonError('Post not found', 404);
            }

            // Only Admin or User who wrote the post are allowed to delete it
            $userJwtData = $this->checkAuthorization();
            if ($userJwtData->role === self::ADMIN || $userJwtData->userId === $post['user_id']) {
                Post::delete($id);
                $this->jsonResponse(['message' => 'Post deleted']);
            } else {
                $this->jsonError('Not authorized', 403);
            } 
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }
}
