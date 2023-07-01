<?php

namespace App\Auth;

use Firebase\JWT\JWT;
use App\Models\User;

class Authentication {
    private $jwtKey;

    public function __construct() {
        $this->jwtKey = $_ENV['JWT_SECRET'];
    }

    public function authenticate($email, $password) {
        $user = User::findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $payload = [
                "iat" => time(), // Issued at
                "exp" => time() + (60 * 60), // Token valid for 1 hour
                "data" => [
                    "userId" => $user['id'],
                    "role" => $user['role'],
                ]
            ];

            $jwt = JWT::encode($payload, $this->jwtKey, 'HS256'); // HS256: algorithm to sign the token
            return $jwt;
        }

        return false;
    }
}
