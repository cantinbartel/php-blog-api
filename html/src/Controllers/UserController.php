<?php

namespace App\Controllers;
use App\Auth\Authorization;

use App\Models\User;

class UserController extends BaseController {

    private function checkAuthorization() {
            // Get JWT from the Authorization header
            $authHeader = getallheaders()['Authorization'] ?? '';
            // Extract JWT from 'Bearer {token}' format
            $jwt = str_replace('Bearer ', '', $authHeader);

            $auth = new Authorization();
            // Validate the JWT and get the user data
            $userData = $auth->authorize($jwt);
          
            if (!$userData) {
                $this->jsonError('Not authorized', 403);
                exit();
            };
    }

    // Get all users
    public function index() {
        try {
            $this->checkAuthorization();
            $users = User::getAll();
            $this->jsonResponse($users);
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        } 
    }

    // Get a user by id
    public function show($id) {
        try {
            $user = User::find($id);
            if ($user) {
                $this->jsonResponse($user);
            } else {
                $this->jsonError('User not found', 404);
            }   
        } catch(\Exception $e) {
            $this->jsonError('Database error: '. $e->getMessage());
        }
    }

    // Create a new user
    public function store() {
        try {
            // Get JSON input
            $data = $this->getJsonInput();
            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT); 
            // Create user
            $result = User::create($data);
            $this->jsonResponse(['message' => 'User created', 'status' => $result], 201);
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }

    // Update a user
    public function update($id) {
        try {
            // Get JSON input
            $data = $this->getJsonInput();
            // Update user
            $user = User::update($id, $data);
            $user = User::find($id);
            $this->jsonResponse($user);
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }

    // Delete a user
    public function destroy($id) {
        try {
            $this->checkAuthorization();
            // Delete the user
            $user = User::delete($id);
            $this->jsonResponse($user);
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }
}
