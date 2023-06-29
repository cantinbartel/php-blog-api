<?php

namespace App\Routes;
use App\Routes\Router;
use App\Controllers\UserController;

class UserRoutes extends Router {
    function __construct() {
       $userController = new UserController();
       
       $this->routes = [
           'GET' => [
               'users' => [$userController, 'index'],
               'users/([a-f0-9\-]+)' => [$userController, 'show']
           ],
           'POST' => [
               'users' => [$userController, 'store']
           ],
           'PUT' => [
                'users/([a-f0-9\-]+)' => [$userController, 'update']
           ],
           'DELETE' => [
                'users/([a-f0-9\-]+)' => [$userController, 'destroy']
           ]
       ];
    }
}
