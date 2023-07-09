<?php

namespace App\Controllers;

use App\Models\User;

class UserController extends BaseController {

    // Get all users
    public function index() {
        try {
            // Only ADMIN is able to access all the users info
            $userJwtData = $this->checkAuthorization();
            $this->checkIsAdmin($userJwtData);
            $users = User::getAll();
            $this->jsonResponse($users);
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        } 
    }

    // Get a user by id
    public function show($id) {
        try {
            // Only Admin or User who want to access his information who can get user/:id
            $userJwtData = $this->checkAuthorization();
            if ($userJwtData->role === self::ADMIN || $userJwtData->userId === $id) {
                $user = User::find($id);
                if ($user) {
                    $this->jsonResponse($user);
                } else {
                    $this->jsonError('User not found', 404);
                }
            } else {
                $this->jsonError('Not authorized', 403);
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
            $result = User::create($data);
            $this->jsonResponse(['message' => 'User created', 'status' => $result], 201);
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }

    // Update a user
    public function update($id) {
        try {
            // Only ADMIN is able to update a user
            $userJwtData = $this->checkAuthorization();
            $this->checkIsAdmin($userJwtData);
            // Get JSON input
            $data = $this->getJsonInput();
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
            // Only ADMIN is able to delete a user
            $userJwtData = $this->checkAuthorization();
            $this->checkIsAdmin($userJwtData);
            $user = User::delete($id);
            $this->jsonResponse($user);
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }
}
