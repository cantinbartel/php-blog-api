<?php

namespace App\Controllers;

use App\Models\User;

class UserController {

    // Get all users
    public function index() {
        $users = User::getAll();
        header('Content-Type: application/json');
        echo json_encode($users);
    }

    // Get a user by id
    public function show($id) {
        $user = User::find($id);
        header('Content-Type: application/json');
        echo json_encode($user);
    }

    // Create a new user
    public function store() {
        // Get JSON input
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // Create user
        $user = User::create($data);

        header('Content-Type: application/json');
        echo json_encode($user);
    }

    // Update a user
    public function update($id) {
        // Get JSON input
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // Update user
        User::update($id, $data);

        $user = User::find($id);
        header('Content-Type: application/json');
        echo json_encode($user);
    }

    // Delete a user
    public function destroy($id) {
        // Delete the user
        $user = User::delete($id);

        header('Content-Type: application/json');
        echo json_encode(['message' => 'User deleted']);
    }
}
