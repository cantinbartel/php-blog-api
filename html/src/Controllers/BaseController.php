<?php

namespace App\Controllers;

abstract class BaseController {

    // Methods to be implemented by child classes
    abstract public function index();
    abstract public function show($id);
    abstract public function store();
    abstract public function update($id);
    abstract public function destroy($id);

    // Get JSON input
    protected function getJsonInput() {
        $json = file_get_contents('php://input');
        return json_decode($json, true);
    }

    // Return a JSON error response
    protected function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }

    // Return a JSON error response
    protected function jsonError($message, $statusCode = 500) {
        $this->jsonResponse(['error' => $message, $statusCode]);
    }
}
