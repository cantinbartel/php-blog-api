<?php

namespace App\Controllers;

use App\Auth\Authorization;

abstract class BaseController {

    // Methods to be implemented by child classes
    abstract public function index();
    abstract public function show($id);
    abstract public function store();
    abstract public function update($id);
    abstract public function destroy($id);

    // Class constants
    protected const USER = 'USER';
    protected const ADMIN = 'ADMIN';

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

    // Check Authorization
    protected function checkAuthorization($userRole = self::USER) {
        // Get JWT from the Authorization header
        $authHeader = getallheaders()['Authorization'] ?? '';
        
        // Extract JWT from Bearer token format
        $jwt = str_replace('Bearer ', '', $authHeader);

        $auth = new Authorization();

        // Validate the JWT and get the user payload data
        $userJwtData = $auth->authorize($jwt);
      
        if (!$userJwtData) {
            $this->jsonError('Not authorized', 403);
            exit();
        };

        return $userJwtData->data;
    }

    // Check if User's role is ADMIN
    protected function checkIsAdmin($userJwtData) {
        if ($userJwtData->role !== self::ADMIN) {
            $this->jsonError('Not authorized', 403);
            exit();
        }
    }
}
