<?php

namespace App\Routes;

use App\Auth\Authentication;
use App\Routes\Router;

class LoginRoutes extends Router {
    private $auth;

    public function __construct() {
        $this->auth = new Authentication();
        $this->routes['POST'] = [
            'login' => [$this, 'login']
        ];
    }

    public function login() {
        // Parse JSON request body
        $data = $this->getJsonInput();

        if (!isset($data['email']) || !isset($data['password'])) {
            $this->sendResponse(['message' => 'Missing email or password'], 400);
            return;
        }

        $jwt = $this->auth->authenticate($data['email'], $data['password']);

        if ($jwt) {
            $this->sendResponse(['token' => $jwt], 200);
        } else {
            $this->sendResponse(['message' => 'Invalid email or password'], 401);
        }
    }

    private function getJsonInput() {
        return json_decode(file_get_contents('php://input'), true);
    }
}
