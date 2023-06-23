<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class JwtMiddleware {
    private $secret;

    public function __construct($secret) {
        $this->secret = $secret;
    }

    public function __invoke(Request $request, Response $response, $next) {
        $authHeader = $request->getHeader('Authorization');

        if (!$authHeader) {
            // No Authorization header present; unauthorized request
            return $response->withStatus(401);
        }

        $jwt = trim(str_replace('Bearer', '', $authHeader[0]));

        try {
            // If the JWT cannot be decoded, this will throw an exception
            JWT::decode($jwt, $this->secret, ['HS256']);
        } catch (\Exception $e) {
            // Invalid JWT
            return $response->withStatus(401);
        }

        // Call the next middleware/controller action
        $response = $next($request, $response);
        return $response;
    }
}
