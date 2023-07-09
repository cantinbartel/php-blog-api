<?php

namespace App\Controllers;

use App\Models\Category;

class CategoryController extends BaseController {

    // Get all categories
    public function index() {
        try {
            $categories = Category::getAll();
            $this->jsonResponse($categories);
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }

    // Get a category by id
    public function show($id) {
        try {
            $category = Category::find($id);
            $this->jsonResponse($category);
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }

    // Create a new category
    public function store() {
        try {
            // Only admin can create a category
            $userJwtData = $this->checkAuthorization();
            $this->checkIsAdmin($userJwtData);
            // Get JSON input
            $data = $this->getJsonInput();
            $category = Category::create($data);
            $this->jsonResponse(['message' => 'Category created', 'status' => $category], 201);
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }

    // Update a category
    public function update($id) {
        try {
            // Only admin can update a category
            $userJwtData = $this->checkAuthorization();
            $this->checkIsAdmin($userJwtData);
            // Get JSON input
            $data = $this->getJsonInput();
            Category::update($id, $data);
            $category = Category::find($id);
            $this->jsonResponse($category);
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }

    // Delete a category
    public function destroy($id) {
        try {
            // Only admin can delete a category
            $userJwtData = $this->checkAuthorization();
            $this->checkIsAdmin($userJwtData);
            Category::delete($id);
            $this->jsonResponse(['message' => 'Category deleted']);
        } catch(\Exception $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }
}
