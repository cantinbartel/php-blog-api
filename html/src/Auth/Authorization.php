<?php

namespace App\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use App\Models\User;

class Authorization {
    private $jwtKey;

    public function __construct() {
        $this->jwtKey = $_ENV['JWT_SECRET'];
    }

    public function authorize($jwt) { 
        try {
            $decoded = JWT::decode($jwt, new Key($this->jwtKey, 'HS256'));
            $user = User::find($decoded->data->userId);
            if (!$user) return false;
            return $decoded;
        } catch(ExpiredException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function authorizeAdmin($jwt) {
        try {
            $decoded = JWT::decode($jwt, new Key($this->jwtKey, 'HS256'));
            $user = User::find($decoded->data->userId);
            if (!$user || $user['role'] !== 'ADMIN') return false;
            return $decoded;
        } catch (ExpiredException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
