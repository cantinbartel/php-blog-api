<?php

namespace App\Controllers;

use App\Models\User;

class UserController {

    // Get all users
    public function index() {
        try {
            $users = User::getAll();
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode($users);
        } catch(\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        } 
    }

    // Get a user by id
    public function show($id) {
        try {
            $user = User::find($id);
            if ($user) {
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode($user);
            } else {
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
            }   
        } catch(\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: '. $e->getMessage()]);
        }
    }

    // Create a new user
    public function store() {
        try {
            // Get JSON input
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT); 
            // Create user
            $result = User::create($data);

            header('Content-Type: application/json');
            http_response_code(201);
            echo json_encode(['message' => 'User created', 'status' => $result]);
        } catch(\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    // Update a user
    public function update($id) {
        try {
            // Get JSON input
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            // Update user
            $user = User::update($id, $data);

            $user = User::find($id);
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(['message' => 'User updated', 'user' => $user]);
        } catch(\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    // Delete a user
    public function destroy($id) {
        try {
            // Delete the user
            $user = User::delete($id);

            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(['message' => 'User deleted']);
        } catch(\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
}
