<?php

namespace App\Controllers;

use App\Models\Category;

class CategoryController {

    // Get all categories
    public function index() {
        $categories = Category::getAll();
        header('Content-Type: application/json');
        echo json_encode($categories);
    }

    // Get a category by id
    public function show($id) {
        $category = Category::find($id);
        header('Content-Type: application/json');
        echo json_encode($category);
    }

    // Create a new category
    public function store() {
        // Get JSON input
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // Create category
        $category = Category::create($data);

        header('Content-Type: application/json');
        echo json_encode($category);
    }

    // Update a category
    public function update($id) {
        // Get JSON input
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // Update category
        Category::update($id, $data);

        $category = Category::find($id);
        header('Content-Type: application/json');
        echo json_encode($category);
    }

    // Delete a category
    public function destroy($id) {
        // Delete the category
        $category = Category::delete($id);

        header('Content-Type: application/json');
        echo json_encode(['message' => 'Category deleted']);
    }
}
